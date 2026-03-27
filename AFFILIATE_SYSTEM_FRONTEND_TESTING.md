# Affiliate System: How It Works + Frontend Testing Guide

## 1) How the affiliate system works (backend)

The current affiliate flow has three stages:

1. **Click tracking**
   - Endpoint: `GET /api/v1/affiliate/track?code={affiliate_code}`
   - Records a click row in `affiliate_clicks`.

2. **Attribution on checkout**
   - Endpoint: `POST /api/v1/checkout`
   - Accepts optional `affiliate_code`.
   - Validates the affiliate code and blocks invalid/inactive/self-referral cases.
   - Stores `affiliate_id` and `affiliate_code` on the created `orders` row.

3. **Conversion + commission creation after payment success**
   - Endpoint: `POST /api/v1/payments/webhooks/{provider}`
   - For a successful payment event (`checkout.completed`), it marks order paid.
   - Then creates an `affiliate_conversions` record and increments affiliate `total_earnings`.

---

## 2) What is already wired in the frontend

- Affiliate dashboard page exists at: `/user/affiliate`
  - Fetches affiliate metrics from:
    - `GET /api/v1/affiliate/dashboard`
    - `GET /api/v1/affiliate/conversions`
- It can generate referral links such as:
  - `/?affiliate_code=AFFCODE`

### Important current gap

- Public frontend pages do **not yet automatically**:
  - read `affiliate_code` from URL,
  - call `GET /api/v1/affiliate/track`,
  - persist affiliate attribution for checkout.

- Also, current purchase page posts to `/api/v1/orders`, while affiliate attribution is implemented on `/api/v1/checkout`.

---

## 3) How to test affiliate from frontend right now

### Step A: Create affiliate code

1. Login to Filament admin (`/admin`).
2. Go to **Affiliates**.
3. Create or confirm an active affiliate for **User A** and note its `code`.

### Step B: Track a click

Open this in browser (replace with your code):

```text
http://127.0.0.1:8001/api/v1/affiliate/track?code=YOUR_CODE
```

Expected: `201` with message `Affiliate click tracked.`

### Step C: Create attributed checkout as another user (User B)

1. Login as **User B** on frontend.
2. Open browser devtools console.
3. Run:

```js
const API = "http://127.0.0.1:8001";
const code = "YOUR_CODE";
const token = localStorage.getItem("auth_token") || sessionStorage.getItem("auth_token");

const plansResponse = await fetch(`${API}/api/v1/plans`, {
  headers: { Accept: "application/json" },
});
const plansPayload = await plansResponse.json();
const planId = plansPayload?.data?.[0]?.id;

const checkoutResponse = await fetch(`${API}/api/v1/checkout`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    Authorization: `Bearer ${token}`,
  },
  body: JSON.stringify({
    plan_id: planId,
    provider: "fake",
    affiliate_code: code,
  }),
});

const checkoutPayload = await checkoutResponse.json();
console.log(checkoutPayload);
```

Expected: response contains `data.order.affiliate_id` and `data.order.affiliate_code`.

### Step D: Trigger payment success webhook (for conversion)

Continue in console:

```js
await fetch("http://127.0.0.1:8001/api/v1/payments/webhooks/fake", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  body: JSON.stringify({
    event: "checkout.completed",
    order_public_id: checkoutPayload.data.order.public_id,
    provider_reference: "manual_ref_1",
    subscription_reference: "manual_sub_1",
  }),
});
```

Expected:
- order becomes paid,
- affiliate conversion record is created,
- affiliate earnings increase.

### Step E: Verify in affiliate dashboard

Login back as **User A** and open `/user/affiliate`:
- `clicks_count` should increase,
- `conversions_count` should increase,
- recent conversion rows should appear.

---

## 4) Suggested improvement for full automatic frontend attribution

To make this fully seamless for end users:

1. Capture `affiliate_code` from URL query on landing pages.
2. Call `GET /api/v1/affiliate/track` once per visit/session.
3. Persist affiliate code in cookie/local storage for the configured window.
4. Send stored affiliate code to `POST /api/v1/checkout` during purchase.

This will make affiliate tracking work naturally from shared referral links without manual API calls.
