@extends('legal.partials.layout')

@php
    $title = 'Privacy Policy';
    $alternateRoute = route('legal.terms');
    $alternateLabel = 'Terms & Conditions';
@endphp

@section('legal-content')
    <p>
        Rentkia.com (&ldquo;Rentkia&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;, or &ldquo;our&rdquo;) operates a rental marketplace that connects customers with independent vendor stores.
        This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
    </p>

    <h2>1. Information we collect</h2>
    <p>We may collect the following types of information:</p>
    <ul>
        <li><strong>Account information:</strong> name, mobile number, email address, and password when you register or log in.</li>
        <li><strong>Booking and order details:</strong> rental dates, items requested, delivery or pickup preferences, and payment-related metadata.</li>
        <li><strong>Vendor information:</strong> business name, category, address, GST details, and store content submitted by vendors.</li>
        <li><strong>Technical data:</strong> IP address, browser type, device information, pages visited, and cookies or similar technologies.</li>
        <li><strong>Communications:</strong> messages sent through support channels, OTP verification records, and feedback you provide.</li>
    </ul>

    <h2>2. How we use your information</h2>
    <p>We use collected information to:</p>
    <ul>
        <li>Provide, operate, and maintain the Rentkia platform.</li>
        <li>Process bookings, payments, and vendor subscriptions.</li>
        <li>Verify identity through OTP and prevent fraud or abuse.</li>
        <li>Communicate with you about orders, account activity, and service updates.</li>
        <li>Improve our website, features, and customer experience.</li>
        <li>Comply with applicable laws and respond to lawful requests.</li>
    </ul>

    <h2>3. Sharing of information</h2>
    <p>
        When you place a rental order, relevant details are shared with the vendor fulfilling that order.
        We may also share information with payment processors, cloud hosting providers, analytics partners, and service providers who assist our operations.
        We do not sell your personal information to third parties for their independent marketing purposes.
    </p>

    <h2>4. Vendor storefronts</h2>
    <p>
        Individual vendors on Rentkia may maintain their own privacy policies for their online stores.
        Their handling of customer data in connection with a specific rental is governed by their policies in addition to this platform policy.
    </p>

    <h2>5. Data retention</h2>
    <p>
        We retain personal information for as long as necessary to provide services, resolve disputes, enforce agreements, and meet legal obligations.
        When data is no longer required, we take reasonable steps to delete or anonymize it.
    </p>

    <h2>6. Security</h2>
    <p>
        We implement administrative, technical, and organizational measures designed to protect your information.
        However, no method of transmission over the internet or electronic storage is completely secure, and we cannot guarantee absolute security.
    </p>

    <h2>7. Your choices and rights</h2>
    <p>
        Depending on applicable law, you may request access to, correction of, or deletion of your personal information.
        You may also opt out of non-essential marketing communications.
        To make a request, contact us using the details below.
    </p>

    <h2>8. Cookies</h2>
    <p>
        We use cookies and similar technologies to remember preferences, keep you signed in, and analyze site usage.
        You can control cookies through your browser settings, though some features may not function properly if cookies are disabled.
    </p>

    <h2>9. Children&rsquo;s privacy</h2>
    <p>
        Rentkia is not intended for individuals under 18 years of age.
        We do not knowingly collect personal information from children.
    </p>

    <h2>10. Changes to this policy</h2>
    <p>
        We may update this Privacy Policy from time to time.
        The revised version will be posted on this page with an updated date.
        Continued use of Rentkia after changes constitutes acceptance of the updated policy.
    </p>

    <h2>11. Contact us</h2>
    <p>
        If you have questions about this Privacy Policy, contact us at:
        <a href="mailto:hello@rentkia.com">hello@rentkia.com</a>
    </p>
@endsection
