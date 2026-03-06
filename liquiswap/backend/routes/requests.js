/**
 * Exchange Request Routes
 * Create, manage, and find exchange requests
 */

const express = require('express');
const { body, validationResult, query } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { authenticate } = require('../middleware/auth');
const MatchingService = require('../services/matchingService');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

/**
 * @route   GET /api/requests
 * @desc    Get all active exchange requests (Marketplace)
 * @access  Private
 */
router.get('/', [
  authenticate,
  query('haveType').optional().isIn(['MTN', 'ORANGE', 'CASH']),
  query('wantType').optional().isIn(['MTN', 'ORANGE', 'CASH']),
  query('page').optional().isInt({ min: 1 }),
  query('limit').optional().isInt({ min: 1, max: 50 })
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const filters = {
      haveType: req.query.haveType,
      wantType: req.query.wantType,
      minAmount: req.query.minAmount,
      maxAmount: req.query.maxAmount,
      location: req.query.location,
      page: req.query.page || 1,
      limit: req.query.limit || 20
    };

    const matchingService = new MatchingService(
      prisma, 
      req.app.get('io'),
      req.app.get('connectedUsers')
    );

    const result = await matchingService.getActiveRequests(filters);

    res.status(200).json({
      success: true,
      data: result
    });
  } catch (error) {
    logger.error('Error fetching requests:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch requests'
    });
  }
});

/**
 * @route   GET /api/requests/my
 * @desc    Get user's exchange requests
 * @access  Private
 */
router.get('/my', authenticate, async (req, res) => {
  try {
    const { status, page = 1, limit = 20 } = req.query;
    const skip = (parseInt(page) - 1) * parseInt(limit);

    const where = { ownerId: req.user.id };
    if (status) where.status = status;

    const [requests, total] = await Promise.all([
      prisma.exchangeRequest.findMany({
        where,
        include: {
          matcher: {
            select: {
              id: true,
              name: true,
              avatar: true,
              rating: true
            }
          },
          transaction: {
            select: {
              id: true,
              status: true,
              senderConfirmed: true,
              receiverConfirmed: true
            }
          }
        },
        orderBy: { createdAt: 'desc' },
        skip,
        take: parseInt(limit)
      }),
      prisma.exchangeRequest.count({ where })
    ]);

    res.status(200).json({
      success: true,
      data: {
        requests,
        pagination: {
          page: parseInt(page),
          limit: parseInt(limit),
          total,
          pages: Math.ceil(total / parseInt(limit))
        }
      }
    });
  } catch (error) {
    logger.error('Error fetching user requests:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch requests'
    });
  }
});

/**
 * @route   GET /api/requests/:id
 * @desc    Get single request details
 * @access  Private
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;

    const request = await prisma.exchangeRequest.findUnique({
      where: { id },
      include: {
        owner: {
          select: {
            id: true,
            name: true,
            avatar: true,
            rating: true,
            totalRatings: true
          }
        },
        matcher: {
          select: {
            id: true,
            name: true,
            avatar: true,
            rating: true
          }
        },
        transaction: {
          include: {
            messages: {
              orderBy: { createdAt: 'asc' },
              take: 50
            }
          }
        }
      }
    });

    if (!request) {
      return res.status(404).json({
        success: false,
        message: 'Request not found'
      });
    }

    res.status(200).json({
      success: true,
      data: { request }
    });
  } catch (error) {
    logger.error('Error fetching request:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch request'
    });
  }
});

/**
 * @route   POST /api/requests
 * @desc    Create new exchange request
 * @access  Private
 */
router.post('/', [
  authenticate,
  body('haveType')
    .notEmpty().withMessage('Have type is required')
    .isIn(['MTN', 'ORANGE', 'CASH']).withMessage('Invalid currency type'),
  body('wantType')
    .notEmpty().withMessage('Want type is required')
    .isIn(['MTN', 'ORANGE', 'CASH']).withMessage('Invalid currency type'),
  body('haveAmount')
    .notEmpty().withMessage('Have amount is required')
    .isFloat({ min: 100 }).withMessage('Minimum amount is 100 XAF'),
  body('wantAmount')
    .notEmpty().withMessage('Want amount is required')
    .isFloat({ min: 100 }).withMessage('Minimum amount is 100 XAF'),
  body('expiresInMinutes')
    .optional()
    .isInt({ min: 5, max: 1440 }).withMessage('Expiry must be between 5 minutes and 24 hours')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { haveType, wantType } = req.body;

    // Validate have and want are different
    if (haveType === wantType) {
      return res.status(400).json({
        success: false,
        message: 'Have and want types must be different'
      });
    }

    const matchingService = new MatchingService(
      prisma,
      req.app.get('io'),
      req.app.get('connectedUsers')
    );

    const request = await matchingService.createRequest(req.user.id, req.body);

    // Try to find immediate match
    const match = await matchingService.findMatch(request);

    if (match) {
      const transaction = await matchingService.createMatchTransaction(request, match);
      
      // Notify via socket
      const io = req.app.get('io');
      
      io.to(`user:${request.ownerId}`).emit('match_found', {
        request,
        match,
        transaction,
        message: "It's a Match! We found someone for your exchange."
      });
      
      io.to(`user:${match.ownerId}`).emit('match_found', {
        request: match,
        match: request,
        transaction,
        message: "It's a Match! We found someone for your exchange."
      });

      return res.status(201).json({
        success: true,
        message: 'Request created and matched!',
        data: {
          request,
          matched: true,
          match,
          transaction
        }
      });
    }

    res.status(201).json({
      success: true,
      message: 'Request created successfully. Waiting for a match.',
      data: {
        request,
        matched: false
      }
    });
  } catch (error) {
    logger.error('Error creating request:', error);
    
    if (error.message.includes('Insufficient')) {
      return res.status(400).json({
        success: false,
        message: error.message
      });
    }

    res.status(500).json({
      success: false,
      message: 'Failed to create request'
    });
  }
});

/**
 * @route   POST /api/requests/:id/match
 * @desc    Manually match with a request
 * @access  Private
 */
router.post('/:id/match', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user.id;

    // Get the request to match with
    const targetRequest = await prisma.exchangeRequest.findUnique({
      where: { id },
      include: { owner: true }
    });

    if (!targetRequest) {
      return res.status(404).json({
        success: false,
        message: 'Request not found'
      });
    }

    if (targetRequest.ownerId === userId) {
      return res.status(400).json({
        success: false,
        message: 'Cannot match with your own request'
      });
    }

    if (targetRequest.status !== 'PENDING') {
      return res.status(400).json({
        success: false,
        message: 'This request is no longer available'
      });
    }

    // Check if user has a complementary request
    const userRequest = await prisma.exchangeRequest.findFirst({
      where: {
        ownerId: userId,
        status: 'PENDING',
        haveType: targetRequest.wantType,
        wantType: targetRequest.haveType
      }
    });

    if (!userRequest) {
      // Create a new request that matches
      const matchingService = new MatchingService(
        prisma,
        req.app.get('io'),
        req.app.get('connectedUsers')
      );

      const newRequest = await matchingService.createRequest(userId, {
        haveType: targetRequest.wantType,
        haveAmount: targetRequest.wantAmount,
        wantType: targetRequest.haveType,
        wantAmount: targetRequest.haveAmount,
        expiresInMinutes: 60
      });

      const transaction = await matchingService.createMatchTransaction(newRequest, targetRequest);

      return res.status(200).json({
        success: true,
        message: 'Match created successfully!',
        data: {
          request: newRequest,
          match: targetRequest,
          transaction
        }
      });
    }

    // Use existing request
    const matchingService = new MatchingService(
      prisma,
      req.app.get('io'),
      req.app.get('connectedUsers')
    );

    const transaction = await matchingService.createMatchTransaction(userRequest, targetRequest);

    res.status(200).json({
      success: true,
      message: 'Match created successfully!',
      data: {
        request: userRequest,
        match: targetRequest,
        transaction
      }
    });
  } catch (error) {
    logger.error('Error creating match:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create match'
    });
  }
});

/**
 * @route   DELETE /api/requests/:id
 * @desc    Cancel a request
 * @access  Private
 */
router.delete('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user.id;

    const request = await prisma.exchangeRequest.findFirst({
      where: { id, ownerId: userId }
    });

    if (!request) {
      return res.status(404).json({
        success: false,
        message: 'Request not found'
      });
    }

    if (request.status !== 'PENDING') {
      return res.status(400).json({
        success: false,
        message: 'Cannot cancel a request that is already matched or completed'
      });
    }

    await prisma.exchangeRequest.update({
      where: { id },
      data: { status: 'CANCELLED' }
    });

    res.status(200).json({
      success: true,
      message: 'Request cancelled successfully'
    });
  } catch (error) {
    logger.error('Error cancelling request:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to cancel request'
    });
  }
});

/**
 * @route   GET /api/requests/stats/overview
 * @desc    Get marketplace statistics
 * @access  Private
 */
router.get('/stats/overview', authenticate, async (req, res) => {
  try {
    const [
      totalActive,
      byType,
      recentMatches,
      averageRates
    ] = await Promise.all([
      prisma.exchangeRequest.count({
        where: { status: 'PENDING', expiresAt: { gt: new Date() } }
      }),
      prisma.exchangeRequest.groupBy({
        by: ['haveType', 'wantType'],
        where: { status: 'PENDING', expiresAt: { gt: new Date() } },
        _count: true,
        _avg: { exchangeRate: true }
      }),
      prisma.exchangeRequest.count({
        where: { 
          status: 'MATCHED',
          matchedAt: { gte: new Date(Date.now() - 24 * 60 * 60 * 1000) }
        }
      }),
      prisma.exchangeRequest.groupBy({
        by: ['haveType', 'wantType'],
        where: { status: 'COMPLETED' },
        _avg: { exchangeRate: true }
      })
    ]);

    res.status(200).json({
      success: true,
      data: {
        totalActive,
        byType,
        recentMatches,
        averageRates
      }
    });
  } catch (error) {
    logger.error('Error fetching stats:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch statistics'
    });
  }
});

module.exports = router;
