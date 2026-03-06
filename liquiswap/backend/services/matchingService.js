/**
 * Matching Service
 * Core algorithm for matching exchange requests
 * Implements efficient pairing based on complementary needs
 */

const logger = require('../utils/logger');

class MatchingService {
  constructor(prisma, io, connectedUsers) {
    this.prisma = prisma;
    this.io = io;
    this.connectedUsers = connectedUsers;
  }

  /**
   * Create a new exchange request
   */
  async createRequest(userId, data) {
    const {
      haveType,
      haveAmount,
      wantType,
      wantAmount,
      location,
      latitude,
      longitude,
      expiresInMinutes = 60
    } = data;

    // Validate user has sufficient balance (for non-cash)
    if (haveType !== 'CASH') {
      const balance = await this.prisma.balance.findUnique({
        where: {
          userId_type: {
            userId,
            type: haveType
          }
        }
      });

      if (!balance || parseFloat(balance.amount) < parseFloat(haveAmount)) {
        throw new Error(`Insufficient ${haveType} balance`);
      }
    }

    // Calculate exchange rate
    const exchangeRate = parseFloat(wantAmount) / parseFloat(haveAmount);

    // Create request
    const request = await this.prisma.exchangeRequest.create({
      data: {
        ownerId: userId,
        haveType,
        haveAmount,
        wantType,
        wantAmount,
        exchangeRate,
        location,
        latitude,
        longitude,
        expiresAt: new Date(Date.now() + expiresInMinutes * 60 * 1000),
        status: 'PENDING'
      },
      include: {
        owner: {
          select: {
            id: true,
            name: true,
            avatar: true,
            rating: true
          }
        }
      }
    });

    logger.info(`Exchange request created: ${request.id} by user ${userId}`);
    
    // Schedule expiry check
    this.scheduleExpiryCheck(request.id, expiresInMinutes);

    return request;
  }

  /**
   * Find a matching request
   * Looks for complementary requests with compatible terms
   */
  async findMatch(request) {
    const {
      haveType,
      wantType,
      haveAmount,
      wantAmount,
      ownerId,
      location,
      latitude,
      longitude
    } = request;

    // Build match query
    const matchQuery = {
      status: 'PENDING',
      ownerId: { not: ownerId }, // Exclude own requests
      // Complementary: they have what we want, want what we have
      haveType: wantType,
      wantType: haveType,
      expiresAt: { gt: new Date() } // Not expired
    };

    // Find potential matches
    const potentialMatches = await this.prisma.exchangeRequest.findMany({
      where: matchQuery,
      include: {
        owner: {
          select: {
            id: true,
            name: true,
            avatar: true,
            rating: true
          }
        }
      },
      orderBy: [
        { createdAt: 'asc' } // FIFO - first come first served
      ],
      take: 10
    });

    if (potentialMatches.length === 0) {
      logger.info(`No matches found for request ${request.id}`);
      return null;
    }

    // Score and rank matches
    const scoredMatches = potentialMatches.map(match => {
      let score = 0;

      // Exact amount match (highest priority)
      const amountDiff = Math.abs(
        parseFloat(match.haveAmount) - parseFloat(wantAmount)
      );
      const amountTolerance = parseFloat(wantAmount) * 0.05; // 5% tolerance

      if (amountDiff <= amountTolerance) {
        score += 100;
      } else {
        score += Math.max(0, 100 - (amountDiff / parseFloat(wantAmount)) * 100);
      }

      // Location proximity (if location data available)
      if (latitude && longitude && match.latitude && match.longitude) {
        const distance = this.calculateDistance(
          latitude, longitude,
          match.latitude, match.longitude
        );
        // Closer is better (max 20 points for distance)
        score += Math.max(0, 20 - distance);
      }

      // User rating (max 10 points)
      score += (match.owner.rating || 5) * 2;

      return { match, score };
    });

    // Sort by score (descending)
    scoredMatches.sort((a, b) => b.score - a.score);

    // Return the best match if score is acceptable
    const bestMatch = scoredMatches[0];
    if (bestMatch.score >= 50) {
      logger.info(`Match found for request ${request.id}: ${bestMatch.match.id} (score: ${bestMatch.score})`);
      return bestMatch.match;
    }

    logger.info(`No suitable match found for request ${request.id} (best score: ${bestMatch.score})`);
    return null;
  }

  /**
   * Create a transaction from matched requests
   */
  async createMatchTransaction(request1, request2) {
    // Determine sender and receiver
    // The one who created the request first is the "sender"
    const sender = request1.createdAt < request2.createdAt ? request1 : request2;
    const receiver = request1.createdAt < request2.createdAt ? request2 : request1;

    // Create transaction
    const transaction = await this.prisma.transaction.create({
      data: {
        requestId: sender.id,
        senderId: sender.ownerId,
        receiverId: receiver.ownerId,
        senderAmount: sender.haveAmount,
        receiverAmount: receiver.haveAmount,
        status: 'PENDING'
      },
      include: {
        sender: {
          select: { id: true, name: true, avatar: true, phone: true }
        },
        receiver: {
          select: { id: true, name: true, avatar: true, phone: true }
        },
        request: true
      }
    });

    // Update both requests to MATCHED status
    await this.prisma.exchangeRequest.update({
      where: { id: request1.id },
      data: { 
        status: 'MATCHED',
        matcherId: request2.ownerId,
        matchedAt: new Date()
      }
    });

    await this.prisma.exchangeRequest.update({
      where: { id: request2.id },
      data: { 
        status: 'MATCHED',
        matcherId: request1.ownerId,
        matchedAt: new Date()
      }
    });

    logger.info(`Transaction created: ${transaction.id} for requests ${request1.id} & ${request2.id}`);

    return transaction;
  }

  /**
   * Execute fund transfer after both parties confirm
   * In production, this would integrate with MTN/Orange APIs
   */
  async executeFundTransfer(transaction) {
    try {
      const { senderId, receiverId, senderAmount, receiverAmount, request } = transaction;

      // Get request details
      const exchangeRequest = await this.prisma.exchangeRequest.findUnique({
        where: { id: transaction.requestId }
      });

      if (!exchangeRequest) {
        throw new Error('Exchange request not found');
      }

      // Use Prisma transaction to ensure all updates happen atomically
      await this.prisma.$transaction(async (tx) => {
        // Deduct from sender's "have" balance
        await tx.balance.update({
          where: {
            userId_type: {
              userId: senderId,
              type: exchangeRequest.haveType
            }
          },
          data: {
            amount: {
              decrement: parseFloat(senderAmount)
            }
          }
        });

        // Add to sender's "want" balance
        await tx.balance.upsert({
          where: {
            userId_type: {
              userId: senderId,
              type: exchangeRequest.wantType
            }
          },
          update: {
            amount: {
              increment: parseFloat(receiverAmount)
            }
          },
          create: {
            userId: senderId,
            type: exchangeRequest.wantType,
            amount: parseFloat(receiverAmount)
          }
        });

        // Deduct from receiver's "have" balance (what they gave)
        await tx.balance.update({
          where: {
            userId_type: {
              userId: receiverId,
              type: exchangeRequest.wantType
            }
          },
          data: {
            amount: {
              decrement: parseFloat(receiverAmount)
            }
          }
        });

        // Add to receiver's "want" balance (what they received)
        await tx.balance.upsert({
          where: {
            userId_type: {
              userId: receiverId,
              type: exchangeRequest.haveType
            }
          },
          update: {
            amount: {
              increment: parseFloat(senderAmount)
            }
          },
          create: {
            userId: receiverId,
            type: exchangeRequest.haveType,
            amount: parseFloat(senderAmount)
          }
        });
      });

      logger.info(`Fund transfer completed for transaction ${transaction.id}`);

      // TODO: Integrate with actual payment APIs
      // - MTN Mobile Money API
      // - Orange Money API

    } catch (error) {
      logger.error(`Fund transfer failed for transaction ${transaction.id}:`, error);
      throw error;
    }
  }

  /**
   * Schedule expiry check for a request
   */
  scheduleExpiryCheck(requestId, expiresInMinutes) {
    setTimeout(async () => {
      try {
        const request = await this.prisma.exchangeRequest.findUnique({
          where: { id: requestId }
        });

        if (request && request.status === 'PENDING') {
          await this.prisma.exchangeRequest.update({
            where: { id: requestId },
            data: { status: 'EXPIRED' }
          });

          // Notify owner
          const socketId = this.connectedUsers.get(request.ownerId);
          if (socketId) {
            this.io.to(socketId).emit('request_expired', {
              requestId,
              message: 'Your exchange request has expired'
            });
          }

          logger.info(`Request ${requestId} expired`);
        }
      } catch (error) {
        logger.error(`Error checking expiry for request ${requestId}:`, error);
      }
    }, expiresInMinutes * 60 * 1000);
  }

  /**
   * Calculate distance between two coordinates (Haversine formula)
   * Returns distance in kilometers
   */
  calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = this.toRadians(lat2 - lat1);
    const dLon = this.toRadians(lon2 - lon1);
    
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    return R * c;
  }

  toRadians(degrees) {
    return degrees * (Math.PI / 180);
  }

  /**
   * Get active requests with optional filtering
   */
  async getActiveRequests(filters = {}) {
    const {
      haveType,
      wantType,
      minAmount,
      maxAmount,
      location,
      page = 1,
      limit = 20
    } = filters;

    const where = {
      status: 'PENDING',
      expiresAt: { gt: new Date() }
    };

    if (haveType) where.haveType = haveType;
    if (wantType) where.wantType = wantType;
    if (minAmount || maxAmount) {
      where.haveAmount = {};
      if (minAmount) where.haveAmount.gte = parseFloat(minAmount);
      if (maxAmount) where.haveAmount.lte = parseFloat(maxAmount);
    }
    if (location) where.location = { contains: location, mode: 'insensitive' };

    const skip = (parseInt(page) - 1) * parseInt(limit);

    const [requests, total] = await Promise.all([
      this.prisma.exchangeRequest.findMany({
        where,
        include: {
          owner: {
            select: {
              id: true,
              name: true,
              avatar: true,
              rating: true
            }
          }
        },
        orderBy: { createdAt: 'desc' },
        skip,
        take: parseInt(limit)
      }),
      this.prisma.exchangeRequest.count({ where })
    ]);

    return {
      requests,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total,
        pages: Math.ceil(total / parseInt(limit))
      }
    };
  }
}

module.exports = MatchingService;
