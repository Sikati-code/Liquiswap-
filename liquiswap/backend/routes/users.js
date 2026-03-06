/**
 * User Routes
 * Profile management, balances, and user settings
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { authenticate } = require('../middleware/auth');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

/**
 * @route   GET /api/users/profile
 * @desc    Get user profile
 * @access  Private
 */
router.get('/profile', authenticate, async (req, res) => {
  try {
    const user = await prisma.user.findUnique({
      where: { id: req.user.id },
      include: {
        balances: true,
        _count: {
          select: {
            transactions: true,
            requests: true
          }
        }
      }
    });

    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.status(200).json({
      success: true,
      data: { user }
    });
  } catch (error) {
    logger.error('Error fetching profile:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch profile'
    });
  }
});

/**
 * @route   PUT /api/users/profile
 * @desc    Update user profile
 * @access  Private
 */
router.put('/profile', [
  authenticate,
  body('name')
    .optional()
    .trim()
    .isLength({ min: 2, max: 50 }).withMessage('Name must be 2-50 characters'),
  body('email')
    .optional()
    .isEmail().withMessage('Invalid email format')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { name, email, avatar } = req.body;
    const updateData = {};

    if (name !== undefined) updateData.name = name;
    if (email !== undefined) updateData.email = email;
    if (avatar !== undefined) updateData.avatar = avatar;

    const user = await prisma.user.update({
      where: { id: req.user.id },
      data: updateData,
      include: {
        balances: true
      }
    });

    res.status(200).json({
      success: true,
      message: 'Profile updated successfully',
      data: { user }
    });
  } catch (error) {
    logger.error('Error updating profile:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update profile'
    });
  }
});

/**
 * @route   GET /api/users/balances
 * @desc    Get user balances
 * @access  Private
 */
router.get('/balances', authenticate, async (req, res) => {
  try {
    const balances = await prisma.balance.findMany({
      where: { userId: req.user.id }
    });

    res.status(200).json({
      success: true,
      data: { balances }
    });
  } catch (error) {
    logger.error('Error fetching balances:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch balances'
    });
  }
});

/**
 * @route   PUT /api/users/balances/:type
 * @desc    Update balance (add phone number or location)
 * @access  Private
 */
router.put('/balances/:type', [
  authenticate,
  body('phoneNumber')
    .optional()
    .matches(/^6[0-9]{8}$/).withMessage('Invalid phone number'),
  body('preferredLocations')
    .optional()
    .isArray().withMessage('Locations must be an array')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { type } = req.params;
    const { phoneNumber, preferredLocations } = req.body;

    // Validate currency type
    const validTypes = ['MTN', 'ORANGE', 'CASH'];
    if (!validTypes.includes(type.toUpperCase())) {
      return res.status(400).json({
        success: false,
        message: 'Invalid currency type'
      });
    }

    const updateData = {};
    if (phoneNumber !== undefined) updateData.phoneNumber = phoneNumber;
    if (preferredLocations !== undefined) updateData.preferredLocations = preferredLocations;

    const balance = await prisma.balance.update({
      where: {
        userId_type: {
          userId: req.user.id,
          type: type.toUpperCase()
        }
      },
      data: updateData
    });

    res.status(200).json({
      success: true,
      message: 'Balance updated successfully',
      data: { balance }
    });
  } catch (error) {
    logger.error('Error updating balance:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update balance'
    });
  }
});

/**
 * @route   GET /api/users/transactions
 * @desc    Get user transaction history
 * @access  Private
 */
router.get('/transactions', authenticate, async (req, res) => {
  try {
    const { page = 1, limit = 20, status } = req.query;
    const skip = (parseInt(page) - 1) * parseInt(limit);

    const where = {
      OR: [
        { senderId: req.user.id },
        { receiverId: req.user.id }
      ],
      ...(status && { status })
    };

    const [transactions, total] = await Promise.all([
      prisma.transaction.findMany({
        where,
        include: {
          sender: {
            select: { id: true, name: true, avatar: true, rating: true }
          },
          receiver: {
            select: { id: true, name: true, avatar: true, rating: true }
          },
          request: {
            select: {
              haveType: true,
              wantType: true,
              haveAmount: true,
              wantAmount: true
            }
          }
        },
        orderBy: { createdAt: 'desc' },
        skip,
        take: parseInt(limit)
      }),
      prisma.transaction.count({ where })
    ]);

    res.status(200).json({
      success: true,
      data: {
        transactions,
        pagination: {
          page: parseInt(page),
          limit: parseInt(limit),
          total,
          pages: Math.ceil(total / parseInt(limit))
        }
      }
    });
  } catch (error) {
    logger.error('Error fetching transactions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch transactions'
    });
  }
});

/**
 * @route   GET /api/users/stats
 * @desc    Get user statistics
 * @access  Private
 */
router.get('/stats', authenticate, async (req, res) => {
  try {
    const userId = req.user.id;

    const [
      totalTransactions,
      completedTransactions,
      totalVolume,
      requestsCreated
    ] = await Promise.all([
      prisma.transaction.count({
        where: {
          OR: [{ senderId: userId }, { receiverId: userId }]
        }
      }),
      prisma.transaction.count({
        where: {
          OR: [{ senderId: userId }, { receiverId: userId }],
          status: 'COMPLETED'
        }
      }),
      prisma.transaction.aggregate({
        where: {
          OR: [{ senderId: userId }, { receiverId: userId }],
          status: 'COMPLETED'
        },
        _sum: {
          senderAmount: true
        }
      }),
      prisma.exchangeRequest.count({
        where: { ownerId: userId }
      })
    ]);

    res.status(200).json({
      success: true,
      data: {
        totalTransactions,
        completedTransactions,
        completionRate: totalTransactions > 0 
          ? Math.round((completedTransactions / totalTransactions) * 100) 
          : 0,
        totalVolume: totalVolume._sum.senderAmount || 0,
        requestsCreated
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

/**
 * @route   POST /api/users/kyc
 * @desc    Submit KYC information
 * @access  Private
 */
router.post('/kyc', [
  authenticate,
  body('idType')
    .notEmpty().withMessage('ID type is required')
    .isIn(['PASSPORT', 'ID_CARD', 'DRIVER_LICENSE']).withMessage('Invalid ID type'),
  body('idNumber')
    .notEmpty().withMessage('ID number is required')
    .trim()
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { idType, idNumber } = req.body;

    const user = await prisma.user.update({
      where: { id: req.user.id },
      data: {
        idType,
        idNumber,
        idVerifiedAt: new Date() // In production, this would be pending admin review
      }
    });

    res.status(200).json({
      success: true,
      message: 'KYC submitted successfully',
      data: { user }
    });
  } catch (error) {
    logger.error('Error submitting KYC:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to submit KYC'
    });
  }
});

module.exports = router;
