/**
 * Notification Service
 * Handles push notifications and in-app notifications
 */

const logger = require('../utils/logger');

class NotificationService {
  constructor(prisma, connectedUsers) {
    this.prisma = prisma;
    this.connectedUsers = connectedUsers;
  }

  /**
   * Send notification to a user
   * Saves to database and sends push if user is online
   */
  async sendNotification(userId, notification) {
    try {
      const { type, title, body, data } = notification;

      // Save to database
      const savedNotification = await this.prisma.notification.create({
        data: {
          userId,
          type,
          title,
          body,
          data: data || {}
        }
      });

      // Check if user is online
      const socketId = this.connectedUsers.get(userId);
      
      if (socketId) {
        // Send real-time notification
        const io = global.io || require('../index').io;
        io.to(`user:${userId}`).emit('notification', {
          id: savedNotification.id,
          type,
          title,
          body,
          data,
          createdAt: savedNotification.createdAt
        });
      }

      // Send push notification (in production)
      await this.sendPushNotification(userId, { title, body, data });

      logger.info(`Notification sent to user ${userId}: ${title}`);
      
      return savedNotification;
    } catch (error) {
      logger.error(`Error sending notification to user ${userId}:`, error);
      throw error;
    }
  }

  /**
   * Send push notification via FCM/APNs
   */
  async sendPushNotification(userId, payload) {
    try {
      // Get user's device tokens
      const deviceTokens = await this.prisma.deviceToken.findMany({
        where: {
          userId,
          isActive: true
        }
      });

      if (deviceTokens.length === 0) {
        return;
      }

      // In production, integrate with Firebase Cloud Messaging or OneSignal
      // Example FCM integration:
      /*
      const admin = require('firebase-admin');
      
      const message = {
        notification: {
          title: payload.title,
          body: payload.body
        },
        data: payload.data || {},
        tokens: deviceTokens.map(t => t.token)
      };

      const response = await admin.messaging().sendMulticast(message);
      logger.info(`Push notification sent: ${response.successCount} successful, ${response.failureCount} failed`);
      */

      logger.info(`Would send push notification to ${deviceTokens.length} devices for user ${userId}`);
    } catch (error) {
      logger.error('Error sending push notification:', error);
    }
  }

  /**
   * Register device token
   */
  async registerDeviceToken(userId, token, platform) {
    try {
      const deviceToken = await this.prisma.deviceToken.upsert({
        where: { token },
        update: {
          userId,
          platform,
          isActive: true,
          updatedAt: new Date()
        },
        create: {
          userId,
          token,
          platform,
          isActive: true
        }
      });

      logger.info(`Device token registered for user ${userId}`);
      return deviceToken;
    } catch (error) {
      logger.error('Error registering device token:', error);
      throw error;
    }
  }

  /**
   * Unregister device token
   */
  async unregisterDeviceToken(token) {
    try {
      await this.prisma.deviceToken.update({
        where: { token },
        data: { isActive: false }
      });

      logger.info(`Device token unregistered: ${token}`);
    } catch (error) {
      logger.error('Error unregistering device token:', error);
    }
  }

  /**
   * Mark notification as read
   */
  async markAsRead(notificationId, userId) {
    try {
      const notification = await this.prisma.notification.updateMany({
        where: {
          id: notificationId,
          userId
        },
        data: {
          isRead: true,
          readAt: new Date()
        }
      });

      return notification;
    } catch (error) {
      logger.error('Error marking notification as read:', error);
      throw error;
    }
  }

  /**
   * Mark all notifications as read
   */
  async markAllAsRead(userId) {
    try {
      await this.prisma.notification.updateMany({
        where: {
          userId,
          isRead: false
        },
        data: {
          isRead: true,
          readAt: new Date()
        }
      });

      logger.info(`All notifications marked as read for user ${userId}`);
    } catch (error) {
      logger.error('Error marking all notifications as read:', error);
      throw error;
    }
  }

  /**
   * Get user's notifications
   */
  async getNotifications(userId, options = {}) {
    try {
      const { page = 1, limit = 20, unreadOnly = false } = options;
      const skip = (page - 1) * limit;

      const where = {
        userId,
        ...(unreadOnly && { isRead: false })
      };

      const [notifications, total, unreadCount] = await Promise.all([
        this.prisma.notification.findMany({
          where,
          orderBy: { createdAt: 'desc' },
          skip,
          take: limit
        }),
        this.prisma.notification.count({ where }),
        this.prisma.notification.count({
          where: { userId, isRead: false }
        })
      ]);

      return {
        notifications,
        pagination: {
          page,
          limit,
          total,
          pages: Math.ceil(total / limit)
        },
        unreadCount
      };
    } catch (error) {
      logger.error('Error fetching notifications:', error);
      throw error;
    }
  }

  /**
   * Send bulk notification (for system announcements)
   */
  async sendBulkNotification(userIds, notification) {
    try {
      const notifications = await Promise.all(
        userIds.map(userId =>
          this.sendNotification(userId, notification)
        )
      );

      logger.info(`Bulk notification sent to ${userIds.length} users`);
      return notifications;
    } catch (error) {
      logger.error('Error sending bulk notification:', error);
      throw error;
    }
  }
}

module.exports = NotificationService;
