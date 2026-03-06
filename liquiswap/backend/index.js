/**
 * LiquiSwap Backend Server
 * Express + Socket.io + PostgreSQL (Prisma)
 * 
 * Features:
 * - RESTful API
 * - Real-time WebSocket connections
 * - JWT Authentication
 * - Matching Algorithm
 * - Push Notifications
 */

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const rateLimit = require('express-rate-limit');
require('dotenv').config();

const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

// Import routes
const authRoutes = require('./routes/auth');
const userRoutes = require('./routes/users');
const requestRoutes = require('./routes/requests');
const transactionRoutes = require('./routes/transactions');
const chatRoutes = require('./routes/chat');
const notificationRoutes = require('./routes/notifications');

// Import services
const MatchingService = require('./services/matchingService');
const NotificationService = require('./services/notificationService');
const { authenticateSocket } = require('./middleware/auth');
const logger = require('./utils/logger');

// Initialize Express app
const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: process.env.CLIENT_URL || '*',
    methods: ['GET', 'POST'],
    credentials: true
  },
  pingTimeout: 60000,
  pingInterval: 25000
});

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP, please try again later.'
});

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.CLIENT_URL || '*',
  credentials: true
}));
app.use(morgan('combined', { stream: { write: message => logger.info(message.trim()) } }));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(limiter);

// Root endpoint
app.get('/', (req, res) => {
  res.status(200).json({
    message: 'LiquiSwap API is running',
    environment: process.env.NODE_ENV || 'development',
    time: new Date().toISOString()
  });
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.status(200).json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    version: '1.0.0'
  });
});

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/users', userRoutes);
app.use('/api/requests', requestRoutes);
app.use('/api/transactions', transactionRoutes);
app.use('/api/chat', chatRoutes);
app.use('/api/notifications', notificationRoutes);

// Global error handler
app.use((err, req, res, next) => {
  logger.error('Unhandled error:', err);
  res.status(500).json({
    success: false,
    message: 'Internal server error',
    error: process.env.NODE_ENV === 'development' ? err.message : undefined
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    message: 'Route not found'
  });
});

// ============================================
// SOCKET.IO REAL-TIME HANDLING
// ============================================

// Store connected users: { userId: socketId }
const connectedUsers = new Map();

// Initialize services
const matchingService = new MatchingService(prisma, io, connectedUsers);
const notificationService = new NotificationService(prisma, connectedUsers);

// Socket authentication middleware
io.use(authenticateSocket);

io.on('connection', (socket) => {
  const userId = socket.user.id;
  
  logger.info(`User connected: ${userId} - Socket: ${socket.id}`);
  
  // Register user connection
  connectedUsers.set(userId, socket.id);
  
  // Update user's last login
  prisma.user.update({
    where: { id: userId },
    data: { lastLoginAt: new Date() }
  }).catch(err => logger.error('Failed to update last login:', err));

  // Join user-specific room for targeted notifications
  socket.join(`user:${userId}`);

  // ============================================
  // CHAT EVENTS
  // ============================================
  
  socket.on('join_transaction', (transactionId) => {
    socket.join(`transaction:${transactionId}`);
    logger.info(`User ${userId} joined transaction ${transactionId}`);
  });

  socket.on('leave_transaction', (transactionId) => {
    socket.leave(`transaction:${transactionId}`);
    logger.info(`User ${userId} left transaction ${transactionId}`);
  });

  socket.on('send_message', async (data) => {
    try {
      const { transactionId, content, messageType = 'TEXT' } = data;
      
      // Save message to database
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

      // Broadcast to transaction room
      io.to(`transaction:${transactionId}`).emit('new_message', message);

      // Send notification to other participant
      const transaction = await prisma.transaction.findUnique({
        where: { id: transactionId },
        include: {
          sender: true,
          receiver: true
        }
      });

      if (transaction) {
        const otherUserId = transaction.senderId === userId 
          ? transaction.receiverId 
          : transaction.senderId;
        
        await notificationService.sendNotification(otherUserId, {
          type: 'MESSAGE_RECEIVED',
          title: 'New Message',
          body: `${socket.user.name || 'Someone'} sent you a message`,
          data: { transactionId, messageId: message.id }
        });
      }
    } catch (error) {
      logger.error('Error sending message:', error);
      socket.emit('error', { message: 'Failed to send message' });
    }
  });

  socket.on('typing', (data) => {
    const { transactionId, isTyping } = data;
    socket.to(`transaction:${transactionId}`).emit('user_typing', {
      userId,
      isTyping
    });
  });

  // ============================================
  // TRANSACTION EVENTS
  // ============================================
  
  socket.on('confirm_transaction', async (data) => {
    try {
      const { transactionId } = data;
      
      const transaction = await prisma.transaction.findUnique({
        where: { id: transactionId },
        include: {
          request: true
        }
      });

      if (!transaction) {
        return socket.emit('error', { message: 'Transaction not found' });
      }

      const isSender = transaction.senderId === userId;
      const updateData = isSender 
        ? { senderConfirmed: true, senderConfirmedAt: new Date() }
        : { receiverConfirmed: true, receiverConfirmedAt: new Date() };

      // Update confirmation
      const updatedTransaction = await prisma.transaction.update({
        where: { id: transactionId },
        data: updateData,
        include: {
          sender: { select: { id: true, name: true, avatar: true } },
          receiver: { select: { id: true, name: true, avatar: true } },
          request: true
        }
      });

      // Check if both confirmed
      if (updatedTransaction.senderConfirmed && updatedTransaction.receiverConfirmed) {
        // Complete the transaction
        await prisma.transaction.update({
          where: { id: transactionId },
          data: { 
            status: 'COMPLETED',
            completedAt: new Date()
          }
        });

        // Update request status
        await prisma.exchangeRequest.update({
          where: { id: transaction.requestId },
          data: { status: 'COMPLETED' }
        });

        // Transfer funds (in real app, integrate with payment providers)
        await matchingService.executeFundTransfer(transaction);

        // Notify both parties
        io.to(`transaction:${transactionId}`).emit('transaction_completed', {
          transactionId,
          message: 'Transaction completed successfully!'
        });

        // Send notifications
        await notificationService.sendNotification(transaction.senderId, {
          type: 'TRANSACTION_COMPLETED',
          title: 'Transaction Complete!',
          body: 'Your exchange has been completed successfully.',
          data: { transactionId }
        });

        await notificationService.sendNotification(transaction.receiverId, {
          type: 'TRANSACTION_COMPLETED',
          title: 'Transaction Complete!',
          body: 'Your exchange has been completed successfully.',
          data: { transactionId }
        });
      } else {
        // Notify the other party
        const otherUserId = isSender ? transaction.receiverId : transaction.senderId;
        
        io.to(`user:${otherUserId}`).emit('confirmation_received', {
          transactionId,
          confirmedBy: userId,
          message: 'The other party has confirmed. Waiting for your confirmation.'
        });

        await notificationService.sendNotification(otherUserId, {
          type: 'TRANSACTION_CONFIRMED',
          title: 'Confirmation Received',
          body: 'The other party has confirmed. Please confirm to complete.',
          data: { transactionId }
        });
      }

      socket.emit('confirm_success', { transaction: updatedTransaction });
    } catch (error) {
      logger.error('Error confirming transaction:', error);
      socket.emit('error', { message: 'Failed to confirm transaction' });
    }
  });

  // ============================================
  // MATCHING EVENTS
  // ============================================
  
  socket.on('create_request', async (data) => {
    try {
      const request = await matchingService.createRequest(userId, data);
      socket.emit('request_created', { request });
      
      // Attempt to find match
      const match = await matchingService.findMatch(request);
      
      if (match) {
        // Create transaction
        const transaction = await matchingService.createMatchTransaction(request, match);
        
        // Notify both users
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

        // Send push notifications
        await notificationService.sendNotification(request.ownerId, {
          type: 'MATCH_FOUND',
          title: "It's a Match!",
          body: 'We found someone for your exchange request.',
          data: { transactionId: transaction.id, requestId: request.id }
        });

        await notificationService.sendNotification(match.ownerId, {
          type: 'MATCH_FOUND',
          title: "It's a Match!",
          body: 'We found someone for your exchange request.',
          data: { transactionId: transaction.id, requestId: match.id }
        });
      }
    } catch (error) {
      logger.error('Error creating request:', error);
      socket.emit('error', { message: 'Failed to create request' });
    }
  });

  socket.on('cancel_request', async (requestId) => {
    try {
      const request = await prisma.exchangeRequest.findFirst({
        where: { id: requestId, ownerId: userId }
      });

      if (!request) {
        return socket.emit('error', { message: 'Request not found' });
      }

      await prisma.exchangeRequest.update({
        where: { id: requestId },
        data: { status: 'CANCELLED' }
      });

      socket.emit('request_cancelled', { requestId });
    } catch (error) {
      logger.error('Error cancelling request:', error);
      socket.emit('error', { message: 'Failed to cancel request' });
    }
  });

  // ============================================
  // DISCONNECT HANDLING
  // ============================================
  
  socket.on('disconnect', (reason) => {
    logger.info(`User disconnected: ${userId} - Reason: ${reason}`);
    connectedUsers.delete(userId);
  });
});

// Make io accessible to routes
app.set('io', io);
app.set('connectedUsers', connectedUsers);

// Export for Vercel
app.io = io;
app.connectedUsers = connectedUsers;
module.exports = app;

// Start server (only if not on Vercel)
const PORT = process.env.PORT || 3000;

if (process.env.VERCEL !== '1') {
  server.listen(PORT, () => {
    logger.info(`========================================`);
    logger.info(`🚀 LiquiSwap Server Running`);
    logger.info(`📡 Port: ${PORT}`);
    logger.info(`🌍 Environment: ${process.env.NODE_ENV || 'development'}`);
    logger.info(`📅 Started at: ${new Date().toISOString()}`);
    logger.info(`========================================`);
  });
}

// Graceful shutdown
process.on('SIGTERM', async () => {
  logger.info('SIGTERM received, shutting down gracefully');
  server.close(() => {
    logger.info('Server closed');
  });
  await prisma.$disconnect();
});

process.on('SIGINT', async () => {
  logger.info('SIGINT received, shutting down gracefully');
  server.close(() => {
    logger.info('Server closed');
  });
  await prisma.$disconnect();
});


