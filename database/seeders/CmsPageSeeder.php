<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'content' => <<<'MARKDOWN'
# Terms & Conditions

_Last updated: March 16, 2026_

## 1. Acceptance of Terms

By creating an account or using GoProxy services, you agree to these Terms & Conditions. If you do not agree, you must stop using the services.

## 2. Eligibility and Account Security

You must provide accurate registration details and keep your login credentials secure. You are responsible for all activity under your account.

## 3. Acceptable Use

You agree to use the platform only for lawful business or research purposes. You must not:

- Violate any laws, regulations, or third-party rights.
- Attempt unauthorized access to systems, networks, or data.
- Distribute malware, spam, abusive traffic, or fraudulent requests.
- Use the service in a way that degrades platform stability for others.

## 4. Billing, Plans, and Renewals

Paid plans are billed according to the pricing shown at checkout. Unless canceled before renewal, subscriptions may renew automatically under your selected billing cycle.

## 5. Service Availability

We work to provide reliable uptime and performance but do not guarantee uninterrupted availability. Maintenance windows and upstream network issues may cause temporary interruptions.

## 6. Suspension and Termination

We may suspend or terminate accounts that violate these terms, create security risks, or abuse the platform. You may close your account at any time by contacting support.

## 7. Intellectual Property

All GoProxy trademarks, software, and service materials remain our property or our licensors' property. These terms do not transfer ownership rights.

## 8. Disclaimers and Liability

The service is provided "as is" and "as available." To the maximum extent permitted by law, we disclaim implied warranties and limit liability for indirect, incidental, or consequential damages.

## 9. Indemnification

You agree to indemnify and hold GoProxy harmless against claims arising from your use of the platform, your content, or your violation of applicable law.

## 10. Changes to Terms

We may update these terms from time to time. Material updates will be posted on this page with a revised effective date.

## 11. Contact

Questions about these terms can be sent to support@goproxy.test.
MARKDOWN,
                'meta_title' => 'Terms & Conditions | GoProxy',
                'meta_description' => 'Review the GoProxy terms covering account use, billing, restrictions, and legal responsibilities.',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => <<<'MARKDOWN'
# Privacy Policy

_Last updated: March 16, 2026_

## 1. Overview

This Privacy Policy explains what information GoProxy collects, how we use it, and what choices you have.

## 2. Information We Collect

We may collect:

- Account details, such as name, email address, and login metadata.
- Billing details needed to process payments and maintain subscriptions.
- Usage and diagnostics data, including request volumes, timestamps, and device information.
- Support communications that you send to our team.

## 3. How We Use Information

We use personal information to:

- Provide, secure, and improve the platform.
- Process transactions and send account or billing notices.
- Respond to support requests and service issues.
- Detect abuse, fraud, or policy violations.
- Comply with legal and regulatory obligations.

## 4. Legal Bases for Processing

Where applicable, we process data based on contractual necessity, legitimate business interests, legal obligations, and your consent when required.

## 5. Data Sharing

We do not sell personal information. We may share data with trusted service providers for hosting, payment processing, analytics, and customer support, subject to contractual safeguards.

## 6. Data Retention

We retain information only as long as necessary for the purposes described in this policy, including legal, tax, security, and dispute-resolution requirements.

## 7. Security Measures

We use administrative, technical, and organizational safeguards designed to protect information against unauthorized access, disclosure, alteration, or destruction.

## 8. Your Rights

Depending on your region, you may have rights to access, correct, delete, or export your information, and to object to or restrict certain processing activities.

## 9. International Transfers

Your information may be processed in countries other than your own. We apply reasonable safeguards for cross-border transfers where required.

## 10. Policy Updates

We may update this policy periodically. Any material changes will be reflected here with an updated effective date.

## 11. Contact

For privacy inquiries or requests, contact privacy@goproxy.test.
MARKDOWN,
                'meta_title' => 'Privacy Policy | GoProxy',
                'meta_description' => 'Learn how GoProxy collects, uses, secures, and retains personal data, and how to exercise your privacy rights.',
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => CmsPage::STATUS_PUBLISHED,
                    'published_at' => now()->subMinute(),
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                ]
            );
        }
    }
}
