# LiquiSwap - Equatorial Trust

A production-ready mobile currency exchange platform built with React Native (Expo) and Node.js. LiquiSwap enables instant peer-to-peer exchanges between MTN Mobile Money, Orange Money, and Cash in Cameroon.

![LiquiSwap Logo](./assets/logo.png)

## Features

- **Instant Matching**: Smart algorithm finds exchange partners in seconds
- **Secure Transactions**: PIN verification and escrow-like confirmation system
- **Real-time Chat**: Communicate with exchange partners within the app
- **Beautiful UI**: Cinematic animations with shared element transitions
- **Multi-currency Support**: MTN, Orange, and Cash exchanges
- **Push Notifications**: Get notified of matches and messages

## Tech Stack

### Backend
- **Runtime**: Node.js 18+
- **Framework**: Express.js
- **Database**: PostgreSQL with Prisma ORM
- **Real-time**: Socket.io for WebSocket connections
- **Authentication**: JWT with refresh tokens
- **SMS**: Twilio integration (optional)

### Frontend
- **Framework**: React Native with Expo
- **Navigation**: React Navigation v6
- **Animations**: React Native Reanimated 3
- **State Management**: Zustand
- **Styling**: NativeWind (Tailwind for RN)
- **Icons**: Ionicons

## Project Structure

```
liquiswap/
├── backend/
│   ├── prisma/
│   │   └── schema.prisma       # Database schema
│   ├── routes/
│   │   ├── auth.js             # Authentication routes
│   │   ├── users.js            # User management
│   │   ├── requests.js         # Exchange requests
│   │   ├── transactions.js     # Transaction handling
│   │   ├── chat.js             # Messaging
│   │   └── notifications.js    # Push notifications
│   ├── middleware/
│   │   └── auth.js             # JWT middleware
│   ├── services/
│   │   ├── matchingService.js  # Matching algorithm
│   │   └── notificationService.js
│   ├── utils/
│   │   └── logger.js           # Winston logger
│   ├── index.js                # Express server
│   ├── package.json
│   └── .env.example
│
├── frontend/
│   ├── src/
│   │   ├── screens/
│   │   │   ├── SplashScreen.js
│   │   │   ├── OnboardingScreen.js
│   │   │   ├── LoginScreen.js
│   │   │   ├── OTPScreen.js
│   │   │   ├── HomeScreen.js
│   │   │   ├── CreateRequestScreen.js
│   │   │   ├── MarketplaceScreen.js
│   │   │   └── TransactionDetailScreen.js
│   │   ├── components/
│   │   │   ├── AnimatedButton.js
│   │   │   ├── BalanceCard.js
│   │   │   ├── RequestCard.js
│   │   │   ├── SwapButton.js
│   │   │   └── SkeletonLoader.js
│   │   ├── store/
│   │   │   ├── authStore.js
│   │   │   ├── requestsStore.js
│   │   │   ├── transactionsStore.js
│   │   │   └── socketStore.js
│   │   ├── constants/
│   │   │   └── theme.js
│   │   └── utils/
│   │       └── api.js
│   ├── App.js
│   ├── app.json
│   ├── babel.config.js
│   ├── tailwind.config.js
│   └── package.json
│
└── README.md
```

## Quick Start

### Prerequisites

- Node.js 18+ installed
- PostgreSQL database (local or cloud)
- Expo CLI: `npm install -g expo-cli`
- Android Studio (for Android emulator) or Xcode (for iOS simulator)

### Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Install dependencies:
```bash
npm install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Update `.env` with your configuration:
```env
NODE_ENV=development
PORT=3000
DATABASE_URL="postgresql://username:password@localhost:5432/liquiswap"
JWT_SECRET="your-super-secret-key"
```

5. Set up the database:
```bash
npx prisma migrate dev --name init
npx prisma generate
```

6. Start the server:
```bash
npm run dev
```

The API will be available at `http://localhost:3000`

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create environment file:
```bash
# For local development
echo "EXPO_PUBLIC_API_URL=http://localhost:3000/api" > .env
```

4. Start the Expo development server:
```bash
npx expo start
```

5. Press:
- `i` to open iOS simulator
- `a` to open Android emulator
- Scan QR code with Expo Go app on physical device

## Demo Script for Jury Presentation

### Introduction (30 seconds)
"Good day, jury members. I'm presenting LiquiSwap, a mobile currency exchange platform designed for the Cameroonian market. Our app enables instant peer-to-peer exchanges between MTN Mobile Money, Orange Money, and Cash."

### Splash & Onboarding (45 seconds)
"Let's start with the user experience. Notice the smooth logo animation with shimmer effect. The onboarding uses parallax scrolling - as you swipe, background elements move at different speeds, creating depth. Each screen fades in with a subtle bounce animation."

### Authentication (1 minute)
"For authentication, we use phone-based OTP - perfect for our target market. Watch how the input auto-advances as you type. When the code is verified, a Lottie success animation plays before transitioning to the dashboard."

### Dashboard (1.5 minutes)
"The dashboard showcases our design system. Notice the staggered fade-in animation - header first, then balance cards sliding in, quick actions scaling up, and finally the transaction list. The balance cards use our 'Equatorial Trust' color palette - MTN Yellow, Orange, and Cash Green.

Each card responds to touch with a spring animation - it lifts up and casts a shadow. Pull down to refresh with our custom purple spinner."

### Create Request Flow (1.5 minutes)
"Creating an exchange request is a 4-step process. Step 1: Select what you have. The cards visually respond with border animations. Step 2: Enter amounts with real-time rate calculation. Step 3: Select what you want. Step 4: Review with a summary card that bounces in.

The SWAP button is our hero element - it has a ripple effect on press and transforms into a loading spinner during submission."

### Marketplace (1 minute)
"The marketplace shows all active requests. Each card has a '3D touch' effect - press and hold to see it depress. The filter modal slides down like a curtain with options animating in sequentially.

Our matching algorithm scores potential matches based on amount compatibility, location proximity, and user ratings."

### Transaction & Chat (1.5 minutes)
"When a match is found, both users see a confetti celebration with 'It's a Match!' The transaction detail screen uses shared element transitions - the card expands seamlessly into this screen.

The chat features real-time messaging with typing indicators. The confirmation buttons have a pulse animation to draw attention. Once pressed, they fill with success color and show a checkmark animation."

### Technical Highlights (30 seconds)
"Under the hood, we use Socket.io for real-time communication, Zustand for state management, and React Native Reanimated 3 for 60fps animations. Our matching algorithm runs in O(n) time with intelligent scoring."

### Conclusion (15 seconds)
"LiquiSwap combines beautiful design with robust engineering to solve a real problem in our market. Thank you for your attention."

## API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/send-otp` | Send OTP to phone |
| POST | `/api/auth/verify-otp` | Verify OTP and login |
| POST | `/api/auth/set-pin` | Set transaction PIN |
| POST | `/api/auth/verify-pin` | Verify PIN |
| POST | `/api/auth/refresh` | Refresh access token |
| GET | `/api/auth/me` | Get current user |

### User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users/profile` | Get user profile |
| PUT | `/api/users/profile` | Update profile |
| GET | `/api/users/balances` | Get balances |
| GET | `/api/users/transactions` | Get transaction history |
| GET | `/api/users/stats` | Get user statistics |

### Request Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/requests` | Get marketplace requests |
| GET | `/api/requests/my` | Get user's requests |
| POST | `/api/requests` | Create new request |
| POST | `/api/requests/:id/match` | Match with request |
| DELETE | `/api/requests/:id` | Cancel request |

### Transaction Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/transactions` | Get transactions |
| GET | `/api/transactions/:id` | Get transaction details |
| POST | `/api/transactions/:id/confirm` | Confirm transaction |
| POST | `/api/transactions/:id/dispute` | Raise dispute |
| POST | `/api/transactions/:id/rate` | Rate transaction |

### Chat Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/chat/:id/messages` | Get messages |
| POST | `/api/chat/:id/messages` | Send message |

## WebSocket Events

### Client → Server
- `create_request` - Create exchange request
- `cancel_request` - Cancel request
- `join_transaction` - Join chat room
- `send_message` - Send chat message
- `confirm_transaction` - Confirm transaction
- `typing` - Send typing indicator

### Server → Client
- `match_found` - Match discovered
- `transaction_completed` - Transaction finished
- `confirmation_received` - Other party confirmed
- `new_message` - New chat message
- `user_typing` - Typing indicator
- `notification` - Push notification

## Environment Variables

### Backend
```env
NODE_ENV=development
PORT=3000
DATABASE_URL=postgresql://...
JWT_SECRET=your-secret
TWILIO_SID=optional
TWILIO_TOKEN=optional
TWILIO_PHONE=optional
```

### Frontend
```env
EXPO_PUBLIC_API_URL=http://localhost:3000/api
```

## Testing

### Backend Tests
```bash
cd backend
npm test
```

### Frontend Tests
```bash
cd frontend
npm test
```

## Deployment

### Backend (Railway/Render/Heroku)
1. Push code to GitHub
2. Connect repository to platform
3. Add environment variables
4. Deploy

### Frontend (Expo EAS)
```bash
cd frontend
eas build --platform android
eas build --platform ios
```

## Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open Pull Request

## License

MIT License - see LICENSE file for details

## Contact

- Project Lead: [Your Name]
- Email: [your.email@example.com]
- GitHub: [github.com/yourusername]

---

**Built with love in Cameroon** 🇨🇲
