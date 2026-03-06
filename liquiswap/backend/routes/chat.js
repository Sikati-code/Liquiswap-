/**
 * Chat Routes
 * Messaging between transaction participants
 */

const express = require('express');
const { body, validationResult, query } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { authenticate } = require('../middleware/auth');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

/**
 * @route   GET /api/chat/:transactionId/messages
 * @desc    Get messages for a transaction
 * @access  Private
 */
router.get('/:transactionId/messages', [
  authenticate,
  query('page').optional().isInt({ min: 1 }),
  query('limit').optional().isInt({ min: 1, max: 100 })
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { transactionId } = req.params;
    const { page = 1, limit = 50 } = req.query;
    const skip = (parseInt(page) - 1) * parseInt(limit);
    const userId = req.user.id;

    // Verify user is part of this transaction
    const transaction = await prisma.transaction.findFirst({
      where: {
        id: transactionId,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      }
    });

    if (!transaction) {
      return res.status(403).json({
        success: false,
        message: 'You are not authorized to view these messages'
      });
    }

    const [messages, total] = await Promise.all([
      prisma.message.findMany({
        where: { transactionId },
        include: {
          sender: {
            select: { id: true, name: true, avatar: true }
          }
        },
        orderBy: { createdAt: 'desc' },
        skip,
        take: parseInt(limit)
      }),
      prisma.message.count({ where: { transactionId } })
    ]);

    // Mark unread messages as read
    await prisma.message.updateMany({
      where: {
        transactionId,
        senderId: { not: userId },
        isRead: false
      },
      data: {
        isRead: true,
        readAt: new Date()
      }
    });

    res.status(200).json({
      success: true,
      data: {
        messages: messages.reverse(), // Return in chronological order
        pagination: {
          page: parseInt(page),
          limit: parseInt(limit),
          total,
          pages: Math.ceil(total / parseInt(limit))
        }
      }
    });
  } catch (error) {
    logger.error('Error fetching messages:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch messages'
    });
  }
});

/**
 * @route   POST /api/chat/:transactionId/messages
 * @desc    Send a message
 * @access  Private
 */
router.post('/:transactionId/messages', [
  authenticate,
  body('content')
    .notEmpty().withMessage('Message content is required')
    .isLength({ max: 1000 }).withMessage('Message too long'),
  body('messageType')
    .optional()
    .isIn(['TEXT', 'IMAGE', 'LOCATION', 'SYSTEM'])
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { transactionId } = req.params;
    const { content, messageType = 'TEXT' } = req.body;
    const userId = req.user.id;

    // Verify user is part of this transaction
    const transaction = await prisma.transaction.findFirst({
      where: {
        id: transactionId,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      }
    });

    if (!transaction) {
      return res.status(403).json({
        success: false,
        message: 'You are not authorized to send messages in this transaction'
      });
    }

    // Create message
    const message = await prisma.message.create({
      data: {
        transactionId,
        senderId: userId,
        content,
        messageType
      },
      include: {
        sender: {
          select: { id: true, name: true, avatar: true }
        }
      }
    });

    // Emit to socket
    const io = req.app.get('io');
    io.to(`transaction:${transactionId}`).emit('new_message', message);

    // Send notification to other user
    const otherUserId = transaction.senderId === userId 
      ? transaction.receiverId 
      : transaction.senderId;

    const NotificationService = require('../services/notificationService');
    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    await notificationService.sendNotification(otherUserId, {
      type: 'MESSAGE_RECEIVED',
      title: 'New Message',
      body: `${req.user.name || 'Someone'}: ${content.substring(0, 50)}${content.length > 50 ? '...' : ''}`,
      data: { transactionId, messageId: message.id }
    });

    res.status(201).json({
      success: true,
      data: { message }
    });
  } catch (error) {
    logger.error('Error sending message:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send message'
    });
  }
});

/**
 * @route   POST /api/chat/:transactionId/typing
 * @desc    Send typing indicator
 * @access  Private
 */
router.post('/:transactionId/typing', authenticate, async (req, res) => {
  try {
    const { transactionId } = req.params;
    const { isTyping } = req.body;
    const userId = req.user.id;

    // Verify user is part of this transaction
    const transaction = await prisma.transaction.findFirst({
      where: {
        id: transactionId,
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      }
    });

    if (!transaction) {
      return res.status(403).json({
        success: false,
        message: 'Unauthorized'
      });
    }

    // Emit typing indicator
    const io = req.app.get('io');
    io.to(`transaction:${transactionId}`).emit('user_typing', {
      userId,
      isTyping
    });

    res.status(200).json({ success: true });
  } catch (error) {
    logger.error('Error sending typing indicator:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send typing indicator'
    });
  }
});

/**
 * @route   GET /api/chat/unread
 * @desc    Get unread message count
 * @access  Private
 */
router.get('/unread/count', authenticate, async (req, res) => {
  try {
    const userId = req.user.id;

    // Get all transactions for this user
    const transactions = await prisma.transaction.findMany({
      where: {
        OR: [
          { senderId: userId },
          { receiverId: userId }
        ]
      },
      select: { id: true }
    });

    const transactionIds = transactions.map(t => t.id);

    const unreadCount = await prisma.message.count({
      where: {
        transactionId: { in: transactionIds },
        senderId: { not: userId },
        isRead: false
      }
    });

    res.status(200).json({
      success: true,
      data: { unreadCount }
    });
  } catch (error) {
    logger.error('Error fetching unread count:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch unread count'
    });
  }
});

module.exports = router;
