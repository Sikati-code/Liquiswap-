/**
 * Notification Routes
 * Manage user notifications and device tokens
 */

const express = require('express');
const { body, validationResult, query } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { authenticate } = require('../middleware/auth');
const NotificationService = require('../services/notificationService');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

/**
 * @route   GET /api/notifications
 * @desc    Get user notifications
 * @access  Private
 */
router.get('/', [
  authenticate,
  query('page').optional().isInt({ min: 1 }),
  query('limit').optional().isInt({ min: 1, max: 50 }),
  query('unreadOnly').optional().isBoolean()
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    const result = await notificationService.getNotifications(req.user.id, {
      page: parseInt(req.query.page) || 1,
      limit: parseInt(req.query.limit) || 20,
      unreadOnly: req.query.unreadOnly === 'true'
    });

    res.status(200).json({
      success: true,
      data: result
    });
  } catch (error) {
    logger.error('Error fetching notifications:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch notifications'
    });
  }
});

/**
 * @route   PATCH /api/notifications/:id/read
 * @desc    Mark notification as read
 * @access  Private
 */
router.patch('/:id/read', authenticate, async (req, res) => {
  try {
    const { id } = req.params;

    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    await notificationService.markAsRead(id, req.user.id);

    res.status(200).json({
      success: true,
      message: 'Notification marked as read'
    });
  } catch (error) {
    logger.error('Error marking notification as read:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to mark notification as read'
    });
  }
});

/**
 * @route   PATCH /api/notifications/read-all
 * @desc    Mark all notifications as read
 * @access  Private
 */
router.patch('/read-all', authenticate, async (req, res) => {
  try {
    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    await notificationService.markAllAsRead(req.user.id);

    res.status(200).json({
      success: true,
      message: 'All notifications marked as read'
    });
  } catch (error) {
    logger.error('Error marking all notifications as read:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to mark notifications as read'
    });
  }
});

/**
 * @route   DELETE /api/notifications/:id
 * @desc    Delete a notification
 * @access  Private
 */
router.delete('/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;

    await prisma.notification.deleteMany({
      where: {
        id,
        userId: req.user.id
      }
    });

    res.status(200).json({
      success: true,
      message: 'Notification deleted'
    });
  } catch (error) {
    logger.error('Error deleting notification:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete notification'
    });
  }
});

/**
 * @route   POST /api/notifications/device-token
 * @desc    Register device token for push notifications
 * @access  Private
 */
router.post('/device-token', [
  authenticate,
  body('token').notEmpty().withMessage('Device token is required'),
  body('platform')
    .notEmpty().withMessage('Platform is required')
    .isIn(['ios', 'android']).withMessage('Platform must be ios or android')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { token, platform } = req.body;

    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    await notificationService.registerDeviceToken(req.user.id, token, platform);

    res.status(200).json({
      success: true,
      message: 'Device token registered successfully'
    });
  } catch (error) {
    logger.error('Error registering device token:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to register device token'
    });
  }
});

/**
 * @route   DELETE /api/notifications/device-token
 * @desc    Unregister device token
 * @access  Private
 */
router.delete('/device-token', [
  authenticate,
  body('token').notEmpty().withMessage('Device token is required')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { token } = req.body;

    const notificationService = new NotificationService(
      prisma,
      req.app.get('connectedUsers')
    );

    await notificationService.unregisterDeviceToken(token);

    res.status(200).json({
      success: true,
      message: 'Device token unregistered successfully'
    });
  } catch (error) {
    logger.error('Error unregistering device token:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to unregister device token'
    });
  }
});

/**
 * @route   GET /api/notifications/unread-count
 * @desc    Get unread notification count
 * @access  Private
 */
router.get('/unread-count', authenticate, async (req, res) => {
  try {
    const count = await prisma.notification.count({
      where: {
        userId: req.user.id,
        isRead: false
      }
    });

    res.status(200).json({
      success: true,
      data: { count }
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
