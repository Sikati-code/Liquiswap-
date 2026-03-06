/**
 * Transaction Routes
 * Manage transactions, confirmations, and disputes
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { authenticate } = require('../middleware/auth');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

/**
 * @route   GET /api/transactions
 * @desc    Get user's transactions
 * @access  Private
 */
router.get('/', authenticate, async (req, res) => {
  try {
    const { status, page = 1, limit = 20 } = req.query;
    const skip = (parseInt(page) - 1) * parseInt(limit);
    const userId = req.user.id;

    const where = {
      OR: [
        { senderId: userId },
        { receiverId: userId }
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
          },
          _count: {
            select: { messages: true }
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
 * @route   GET /api/transactions/:id
 * @desc    Get transaction details
 * @access  Private
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user.id;

    const transaction = await prisma.transaction.findFirst({
      where: {
        id,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      },
      include: {
        sender: {
          select: { 
            id: true, 
            name: true, 
            avatar: true, 
            rating: true,
            phone: true
          }
        },
        receiver: {
          select: { 
            id: true, 
            name: true, 
            avatar: true, 
            rating: true,
            phone: true
          }
        },
        request: {
          include: {
            owner: {
              select: { id: true, name: true, avatar: true }
            },
            matcher: {
              select: { id: true, name: true, avatar: true }
            }
          }
        },
        messages: {
          orderBy: { createdAt: 'asc' },
          include: {
            sender: {
              select: { id: true, name: true, avatar: true }
            }
          }
        }
      }
    });

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: 'Transaction not found'
      });
    }

    res.status(200).json({
      success: true,
      data: { transaction }
    });
  } catch (error) {
    logger.error('Error fetching transaction:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch transaction'
    });
  }
});

/**
 * @route   POST /api/transactions/:id/confirm
 * @desc    Confirm transaction receipt
 * @access  Private
 */
router.post('/:id/confirm', [
  authenticate,
  body('pin')
    .notEmpty().withMessage('PIN is required')
    .isLength({ min: 4, max: 6 }).withMessage('PIN must be 4-6 digits')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { id } = req.params;
    const { pin } = req.body;
    const userId = req.user.id;

    // Get transaction
    const transaction = await prisma.transaction.findFirst({
      where: {
        id,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      },
      include: {
        sender: true,
        receiver: true,
        request: true
      }
    });

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: 'Transaction not found'
      });
    }

    if (transaction.status === 'COMPLETED') {
      return res.status(400).json({
        success: false,
        message: 'Transaction already completed'
      });
    }

    if (transaction.status === 'CANCELLED') {
      return res.status(400).json({
        success: false,
        message: 'Transaction has been cancelled'
      });
    }

    // Verify PIN
    const bcrypt = require('bcryptjs');
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: { pinHash: true }
    });

    if (!user.pinHash) {
      return res.status(400).json({
        success: false,
        message: 'PIN not set. Please set a PIN first.'
      });
    }

    const isValidPin = await bcrypt.compare(pin, user.pinHash);

    if (!isValidPin) {
      return res.status(400).json({
        success: false,
        message: 'Invalid PIN'
      });
    }

    // Determine if sender or receiver
    const isSender = transaction.senderId === userId;
    
    // Check if already confirmed
    if (isSender && transaction.senderConfirmed) {
      return res.status(400).json({
        success: false,
        message: 'You have already confirmed this transaction'
      });
    }

    if (!isSender && transaction.receiverConfirmed) {
      return res.status(400).json({
        success: false,
        message: 'You have already confirmed this transaction'
      });
    }

    // Update confirmation
    const updateData = isSender 
      ? { senderConfirmed: true, senderConfirmedAt: new Date() }
      : { receiverConfirmed: true, receiverConfirmedAt: new Date() };

    const updatedTransaction = await prisma.transaction.update({
      where: { id },
      data: updateData,
      include: {
        sender: {
          select: { id: true, name: true, avatar: true }
        },
        receiver: {
          select: { id: true, name: true, avatar: true }
        },
        request: true
      }
    });

    const io = req.app.get('io');

    // Check if both confirmed
    if (updatedTransaction.senderConfirmed && updatedTransaction.receiverConfirmed) {
      // Complete transaction
      const completedTransaction = await prisma.transaction.update({
        where: { id },
        data: { 
          status: 'COMPLETED',
          completedAt: new Date()
        },
        include: {
          sender: { select: { id: true, name: true } },
          receiver: { select: { id: true, name: true } },
          request: true
        }
      });

      // Update request status
      await prisma.exchangeRequest.update({
        where: { id: transaction.requestId },
        data: { status: 'COMPLETED' }
      });

      // Execute fund transfer
      const MatchingService = require('../services/matchingService');
      const matchingService = new MatchingService(
        prisma,
        io,
        req.app.get('connectedUsers')
      );

      await matchingService.executeFundTransfer(completedTransaction);

      // Notify both parties
      io.to(`transaction:${id}`).emit('transaction_completed', {
        transactionId: id,
        message: 'Transaction completed successfully!'
      });

      // Send notifications
      const NotificationService = require('../services/notificationService');
      const notificationService = new NotificationService(
        prisma,
        req.app.get('connectedUsers')
      );

      await notificationService.sendNotification(transaction.senderId, {
        type: 'TRANSACTION_COMPLETED',
        title: 'Transaction Complete!',
        body: 'Your exchange has been completed successfully.',
        data: { transactionId: id }
      });

      await notificationService.sendNotification(transaction.receiverId, {
        type: 'TRANSACTION_COMPLETED',
        title: 'Transaction Complete!',
        body: 'Your exchange has been completed successfully.',
        data: { transactionId: id }
      });

      return res.status(200).json({
        success: true,
        message: 'Transaction completed successfully!',
        data: { transaction: completedTransaction }
      });
    }

    // Notify other party
    const otherUserId = isSender ? transaction.receiverId : transaction.senderId;
    
    io.to(`user:${otherUserId}`).emit('confirmation_received', {
      transactionId: id,
      confirmedBy: userId,
      message: 'The other party has confirmed. Waiting for your confirmation.'
    });

    res.status(200).json({
      success: true,
      message: 'Confirmation recorded. Waiting for other party.',
      data: { transaction: updatedTransaction }
    });
  } catch (error) {
    logger.error('Error confirming transaction:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to confirm transaction'
    });
  }
});

/**
 * @route   POST /api/transactions/:id/dispute
 * @desc    Raise a dispute
 * @access  Private
 */
router.post('/:id/dispute', [
  authenticate,
  body('reason')
    .notEmpty().withMessage('Dispute reason is required')
    .isLength({ min: 10 }).withMessage('Please provide more details')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { id } = req.params;
    const { reason } = req.body;
    const userId = req.user.id;

    const transaction = await prisma.transaction.findFirst({
      where: {
        id,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      }
    });

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: 'Transaction not found'
      });
    }

    if (transaction.status === 'COMPLETED') {
      return res.status(400).json({
        success: false,
        message: 'Cannot dispute a completed transaction'
      });
    }

    if (transaction.disputed) {
      return res.status(400).json({
        success: false,
        message: 'Transaction already under dispute'
      });
    }

    const updatedTransaction = await prisma.transaction.update({
      where: { id },
      data: {
        disputed: true,
        disputeReason: reason,
        status: 'DISPUTED'
      }
    });

    // Notify admins (in production, send email/push to admin panel)
    logger.warn(`Dispute raised for transaction ${id}: ${reason}`);

    res.status(200).json({
      success: true,
      message: 'Dispute raised successfully. Our team will review it.',
      data: { transaction: updatedTransaction }
    });
  } catch (error) {
    logger.error('Error raising dispute:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to raise dispute'
    });
  }
});

/**
 * @route   POST /api/transactions/:id/rate
 * @desc    Rate the other party
 * @access  Private
 */
router.post('/:id/rate', [
  authenticate,
  body('rating')
    .notEmpty().withMessage('Rating is required')
    .isInt({ min: 1, max: 5 }).withMessage('Rating must be 1-5'),
  body('review')
    .optional()
    .isLength({ max: 500 }).withMessage('Review must be under 500 characters')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { id } = req.params;
    const { rating, review } = req.body;
    const userId = req.user.id;

    const transaction = await prisma.transaction.findFirst({
      where: {
        id,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      }
    });

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: 'Transaction not found'
      });
    }

    if (transaction.status !== 'COMPLETED') {
      return res.status(400).json({
        success: false,
        message: 'Can only rate completed transactions'
      });
    }

    const isSender = transaction.senderId === userId;
    const updateData = isSender
      ? { senderRating: rating, senderReview: review }
      : { receiverRating: rating, receiverReview: review };

    await prisma.transaction.update({
      where: { id },
      data: updateData
    });

    // Update other user's rating
    const otherUserId = isSender ? transaction.receiverId : transaction.senderId;
    
    const userTransactions = await prisma.transaction.findMany({
      where: {
        OR: [
          { senderId: otherUserId, senderRating: { not: null } },
          { receiverId: otherUserId, receiverRating: { not: null } }
        ]
      }
    });

    const ratings = userTransactions.map(t => 
      t.senderId === otherUserId ? t.senderRating : t.receiverRating
    ).filter(Boolean);

    const averageRating = ratings.reduce((a, b) => a + b, 0) / ratings.length;

    await prisma.user.update({
      where: { id: otherUserId },
      data: {
        rating: averageRating,
        totalRatings: ratings.length
      }
    });

    res.status(200).json({
      success: true,
      message: 'Rating submitted successfully'
    });
  } catch (error) {
    logger.error('Error submitting rating:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to submit rating'
    });
  }
});

module.exports = router;
