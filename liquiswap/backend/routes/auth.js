/**
 * Authentication Routes
 * Phone-based OTP authentication (MTN/Orange Cameroon style)
 */

const express = require('express');
const bcrypt = require('bcryptjs');
const { body, validationResult } = require('express-validator');
const { PrismaClient } = require('@prisma/client');
const { generateToken, generateRefreshToken, authenticate } = require('../middleware/auth');
const logger = require('../utils/logger');

const router = express.Router();
const prisma = new PrismaClient();

// In-memory OTP store (use Redis in production)
const otpStore = new Map();

/**
 * @route   POST /api/auth/send-otp
 * @desc    Send OTP to phone number
 * @access  Public
 */
router.post('/send-otp', [
  body('phone')
    .notEmpty().withMessage('Phone number is required')
    .matches(/^6[0-9]{8}$/).withMessage('Invalid Cameroon phone number format')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { phone } = req.body;

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    
    // Store OTP with expiry (5 minutes)
    otpStore.set(phone, {
      code: otp,
      expiresAt: Date.now() + 5 * 60 * 1000,
      attempts: 0
    });

    // TODO: Integrate with Twilio or local SMS provider
    // For development, just log the OTP
    logger.info(`OTP for ${phone}: ${otp}`);

    // In production, send actual SMS
    if (process.env.NODE_ENV === 'production') {
      // const twilio = require('twilio');
      // const client = twilio(process.env.TWILIO_SID, process.env.TWILIO_TOKEN);
      // await client.messages.create({
      //   body: `Your LiquiSwap verification code is: ${otp}. Valid for 5 minutes.`,
      //   from: process.env.TWILIO_PHONE,
      //   to: `+237${phone}`
      // });
    }

    res.status(200).json({
      success: true,
      message: 'OTP sent successfully',
      // Only return OTP in development
      ...(process.env.NODE_ENV !== 'production' && { otp })
    });
  } catch (error) {
    logger.error('Error sending OTP:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send OTP'
    });
  }
});

/**
 * @route   POST /api/auth/verify-otp
 * @desc    Verify OTP and login/register user
 * @access  Public
 */
router.post('/verify-otp', [
  body('phone')
    .notEmpty().withMessage('Phone number is required')
    .matches(/^6[0-9]{8}$/).withMessage('Invalid Cameroon phone number format'),
  body('otp')
    .notEmpty().withMessage('OTP is required')
    .isLength({ min: 6, max: 6 }).withMessage('OTP must be 6 digits')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { phone, otp, name } = req.body;

    // Verify OTP
    const storedOtp = otpStore.get(phone);
    
    if (!storedOtp) {
      return res.status(400).json({
        success: false,
        message: 'OTP not found or expired. Please request a new one.'
      });
    }

    if (storedOtp.expiresAt < Date.now()) {
      otpStore.delete(phone);
      return res.status(400).json({
        success: false,
        message: 'OTP expired. Please request a new one.'
      });
    }

    if (storedOtp.code !== otp) {
      storedOtp.attempts++;
      
      if (storedOtp.attempts >= 3) {
        otpStore.delete(phone);
        return res.status(400).json({
          success: false,
          message: 'Too many failed attempts. Please request a new OTP.'
        });
      }

      return res.status(400).json({
        success: false,
        message: 'Invalid OTP. Please try again.',
        attemptsRemaining: 3 - storedOtp.attempts
      });
    }

    // OTP verified - clear from store
    otpStore.delete(phone);

    // Find or create user
    let user = await prisma.user.findUnique({
      where: { phone }
    });

    const isNewUser = !user;

    if (isNewUser) {
      // Create new user and default balances in a transaction
      user = await prisma.$transaction(async (tx) => {
        const newUser = await tx.user.create({
          data: {
            phone,
            name: name || `User${phone.slice(-4)}`,
            isVerified: true
          }
        });

        // Create default balances
        await tx.balance.createMany({
          data: [
            { userId: newUser.id, type: 'MTN', amount: 0 },
            { userId: newUser.id, type: 'ORANGE', amount: 0 },
            { userId: newUser.id, type: 'CASH', amount: 0 }
          ]
        });

        return newUser;
      });

      logger.info(`New user registered: ${phone}`);
    } else {
      // Update existing user
      user = await prisma.user.update({
        where: { id: user.id },
        data: { 
          isVerified: true,
          lastLoginAt: new Date()
        }
      });

      logger.info(`User logged in: ${phone}`);
    }

    // Generate tokens
    const token = generateToken(user.id);
    const refreshToken = generateRefreshToken(user.id);

    // Fetch user with balances
    const userWithBalances = await prisma.user.findUnique({
      where: { id: user.id },
      include: {
        balances: true
      }
    });

    res.status(200).json({
      success: true,
      message: isNewUser ? 'Registration successful' : 'Login successful',
      data: {
        user: userWithBalances,
        isNewUser,
        tokens: {
          accessToken: token,
          refreshToken,
          expiresIn: '30d'
        }
      }
    });
  } catch (error) {
    logger.error('Error verifying OTP:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to verify OTP'
    });
  }
});

/**
 * @route   POST /api/auth/set-pin
 * @desc    Set transaction PIN
 * @access  Private
 */
router.post('/set-pin', [
  authenticate,
  body('pin')
    .notEmpty().withMessage('PIN is required')
    .isLength({ min: 4, max: 6 }).withMessage('PIN must be 4-6 digits')
    .matches(/^\d+$/).withMessage('PIN must contain only digits')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { pin } = req.body;
    const userId = req.user.id;

    // Hash PIN
    const salt = await bcrypt.genSalt(10);
    const pinHash = await bcrypt.hash(pin, salt);

    // Update user
    await prisma.user.update({
      where: { id: userId },
      data: { pinHash }
    });

    res.status(200).json({
      success: true,
      message: 'PIN set successfully'
    });
  } catch (error) {
    logger.error('Error setting PIN:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to set PIN'
    });
  }
});

/**
 * @route   POST /api/auth/verify-pin
 * @desc    Verify transaction PIN
 * @access  Private
 */
router.post('/verify-pin', [
  authenticate,
  body('pin')
    .notEmpty().withMessage('PIN is required')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        errors: errors.array()
      });
    }

    const { pin } = req.body;
    const userId = req.user.id;

    // Fetch user with PIN hash
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

    // Verify PIN
    const isValid = await bcrypt.compare(pin, user.pinHash);

    if (!isValid) {
      return res.status(400).json({
        success: false,
        message: 'Invalid PIN'
      });
    }

    res.status(200).json({
      success: true,
      message: 'PIN verified successfully'
    });
  } catch (error) {
    logger.error('Error verifying PIN:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to verify PIN'
    });
  }
});

/**
 * @route   POST /api/auth/refresh
 * @desc    Refresh access token
 * @access  Public (with refresh token)
 */
router.post('/refresh', async (req, res) => {
  try {
    const { refreshToken } = req.body;

    if (!refreshToken) {
      return res.status(401).json({
        success: false,
        message: 'Refresh token required'
      });
    }

    // Verify refresh token
    const jwt = require('jsonwebtoken');
    const { JWT_SECRET } = require('../middleware/auth');
    
    const decoded = jwt.verify(refreshToken, JWT_SECRET);
    
    if (decoded.type !== 'refresh') {
      return res.status(401).json({
        success: false,
        message: 'Invalid refresh token'
      });
    }

    // Generate new tokens
    const newToken = generateToken(decoded.userId);
    const newRefreshToken = generateRefreshToken(decoded.userId);

    res.status(200).json({
      success: true,
      data: {
        accessToken: newToken,
        refreshToken: newRefreshToken,
        expiresIn: '30d'
      }
    });
  } catch (error) {
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Refresh token expired. Please login again.'
      });
    }

    logger.error('Error refreshing token:', error);
    res.status(401).json({
      success: false,
      message: 'Invalid refresh token'
    });
  }
});

/**
 * @route   POST /api/auth/logout
 * @desc    Logout user (invalidate tokens)
 * @access  Private
 */
router.post('/logout', authenticate, async (req, res) => {
  try {
    // In a real app, you might want to blacklist the token
    // For now, just return success - client will clear tokens
    res.status(200).json({
      success: true,
      message: 'Logout successful'
    });
  } catch (error) {
    logger.error('Error during logout:', error);
    res.status(500).json({
      success: false,
      message: 'Logout failed'
    });
  }
});

/**
 * @route   GET /api/auth/me
 * @desc    Get current user
 * @access  Private
 */
router.get('/me', authenticate, async (req, res) => {
  try {
    const user = await prisma.user.findUnique({
      where: { id: req.user.id },
      include: {
        balances: true
      }
    });

    res.status(200).json({
      success: true,
      data: { user }
    });
  } catch (error) {
    logger.error('Error fetching user:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch user'
    });
  }
});

module.exports = router;
