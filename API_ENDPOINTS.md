# API Endpoints Documentation

Base path: `/api/v1`
Auth type for protected endpoints: `Authorization: Bearer <sanctum_token>`

## Authentication

### Frontend Proxy Guard (Next.js)
- File: `frontend/src/proxy.ts`
- Note: Next.js now uses the `proxy.ts` convention (replacing `middleware.ts`).
- Behavior:
- Redirects `/` to `/dashboard` when authenticated, otherwise to `/login`.
- Redirects unauthenticated access to protected frontend routes (`/dashboard`, `/notifications`, `/tickets`, `/orders`, `/wallet`, `/pricing`, `/affiliate`, `/faq`, `/help-center`) to `/login?redirect={pathname}`.
- Redirects authenticated users away from `/login` and `/register` to `/dashboard`.

### Register
- Method: `POST`
- URL: `/api/v1/register`
- Auth: No
- Body:
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
- Success (`201`):
```json
{
  "message": "User registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "token": "1|...",
    "token_type": "Bearer"
  }
}
```

### Login
- Method: `POST`
- URL: `/api/v1/login`
- Auth: No
- Body:
```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```
- Success (`200`):
```json
{
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "token": "2|...",
    "token_type": "Bearer"
  }
}
```
- Failure (`422`):
```json
{
  "message": "Invalid credentials."
}
```

### GitHub Login Redirect
- Method: `GET`
- URL: `/api/v1/auth/github/redirect`
- Auth: No
- Description: Redirects user to GitHub OAuth consent page.

### GitHub Login Callback
- Method: `GET`
- URL: `/api/v1/auth/github/callback`
- Auth: No
- Description: Exchanges GitHub callback for local user + Sanctum token.
- Success (`200`):
```json
{
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "GitHub User",
      "email": "jane@example.com",
      "github_id": "123456"
    },
    "token": "3|...",
    "token_type": "Bearer"
  }
}
```

## Plans

### List Active Plans
- Method: `GET`
- URL: `/api/v1/plans`
- Auth: No
- Success (`200`):
```json
{
  "data": [
    {
      "id": 1,
      "name": "Monthly Pro",
      "slug": "monthly-pro",
      "description": "Monthly plan",
      "amount": 9900,
      "currency": "USD",
      "interval_unit": "month",
      "interval_count": 1,
      "is_active": true,
      "provider": "fake",
      "provider_price_id": "price_monthly_pro"
    }
  ]
}
```

Plan creation is admin-only via Filament backend and is not exposed as a public API endpoint.

## Checkout and Payments

### Create Checkout Session
- Method: `POST`
- URL: `/api/v1/checkout`
- Auth: Yes
- Body:
```json
{
  "plan_id": 1,
  "provider": "fake",
  "coupon_code": "WELCOME10",
  "affiliate_code": "AFF-ALICE"
}
```
- Notes:
- `plan_id` is required and must be an active plan.
- `provider` is optional. Current configured provider: `fake`.
- `coupon_code` is optional.
- `affiliate_code` is optional.
- Success (`201`):
```json
{
  "message": "Checkout session created successfully.",
  "data": {
    "order": {
      "id": 1,
      "public_id": "01K2...",
      "status": "pending",
      "provider": "fake",
      "currency": "USD",
      "subtotal": 9900,
      "discount_total": 1000,
      "total": 8900,
      "coupon_id": 2,
      "coupon_code": "WELCOME10",
      "affiliate_id": 1,
      "affiliate_code": "AFF-ALICE"
    },
    "checkout_url": "http://localhost/fake-checkout/01K2..."
  }
}
```

### Payment Webhook
- Method: `POST`
- URL: `/api/v1/payments/webhooks/{provider}`
- Auth: No (provider callback endpoint)
- Supported provider now: `fake`
- Body examples:

`checkout.completed` / `invoice.payment_succeeded`
```json
{
  "event": "checkout.completed",
  "order_public_id": "01K2...",
  "provider_reference": "fake_checkout_123",
  "subscription_reference": "sub_123"
}
```

`invoice.payment_failed`
```json
{
  "event": "invoice.payment_failed",
  "order_public_id": "01K2..."
}
```

`customer.subscription.deleted`
```json
{
  "event": "customer.subscription.deleted",
  "order_public_id": "01K2...",
  "subscription_reference": "sub_123"
}
```

- Success (`202`):
```json
{
  "message": "Webhook processed."
}
```

## User Notifications

### List Notifications
- Method: `GET`
- URL: `/api/v1/notifications`
- Auth: Yes
- Success (`200`): paginated notification list.

### Mark Notification as Read
- Method: `POST`
- URL: `/api/v1/notifications/{notification}/read`
- Auth: Yes
- Success (`200`):
```json
{
  "message": "Notification marked as read."
}
```

## Ticketing

### List User Tickets
- Method: `GET`
- URL: `/api/v1/tickets`
- Auth: Yes
- Success (`200`): paginated current user's tickets.

### Create Ticket
- Method: `POST`
- URL: `/api/v1/tickets`
- Auth: Yes
- Body:
```json
{
  "subject": "Cannot upgrade plan",
  "message": "Checkout fails at confirmation.",
  "priority": "medium"
}
```
- Notes:
- `priority` is optional: `low`, `medium`, `high`.
- Success (`201`):
```json
{
  "message": "Ticket created successfully.",
  "data": {
    "id": 1,
    "subject": "Cannot upgrade plan",
    "status": "open",
    "priority": "medium"
  }
}
```

### Show Ticket
- Method: `GET`
- URL: `/api/v1/tickets/{ticket}`
- Auth: Yes
- Notes:
- Only ticket owner can view.

### Reply to Ticket
- Method: `POST`
- URL: `/api/v1/tickets/{ticket}/replies`
- Auth: Yes
- Body:
```json
{
  "message": "Additional details about the issue."
}
```
- Notes:
- Only ticket owner can reply.
- Closed tickets cannot be replied to.

## Affiliate

### Track Affiliate Click
- Method: `GET`
- URL: `/api/v1/affiliate/track?code={affiliate_code}`
- Auth: No
- Success (`201`):
```json
{
  "message": "Affiliate click tracked.",
  "data": {
    "affiliate_code": "AFF-ALICE",
    "affiliate_id": 1
  }
}
```

### Affiliate Dashboard
- Method: `GET`
- URL: `/api/v1/affiliate/dashboard`
- Auth: Yes
- Success (`200`): affiliate profile + stats.

### Affiliate Conversions
- Method: `GET`
- URL: `/api/v1/affiliate/conversions`
- Auth: Yes
- Success (`200`): paginated conversions list.

## Content Endpoints

### List Posts
- Method: `GET`
- URL: `/api/v1/posts`
- Auth: No
- Query params:
- `per_page` (1-50)
- `category` (category slug)
- `tag` (tag slug)
- `search` (text)

### Show Post
- Method: `GET`
- URL: `/api/v1/posts/{slug}`
- Auth: No

### List CMS Pages
- Method: `GET`
- URL: `/api/v1/cms-pages`
- Auth: No
- Query params:
- `per_page` (1-50)
- `search` (text across title, excerpt, content)
- Notes:
- Returns only published pages (`status=published` and `published_at <= now`).

### Show CMS Page
- Method: `GET`
- URL: `/api/v1/cms-pages/{slug}`
- Auth: No
- Notes:
- Returns only published pages; draft/scheduled pages return `404`.

## Default Sanctum User Endpoint

### Current Authenticated User
- Method: `GET`
- URL: `/api/user`
- Auth: Yes (`Bearer <token>`)
