@extends('legal.partials.layout')

@php
    $title = 'Terms & Conditions';
    $alternateRoute = route('legal.privacy');
    $alternateLabel = 'Privacy Policy';
@endphp

@section('legal-content')
    <p>
        Welcome to Rentkia.com. These Terms &amp; Conditions (&ldquo;Terms&rdquo;) govern your access to and use of the Rentkia website, vendor tools, and related services.
        By using Rentkia, you agree to these Terms. If you do not agree, please do not use our platform.
    </p>

    <h2>1. About Rentkia</h2>
    <p>
        Rentkia is a technology platform that enables independent vendors to list rental inventory and enables customers to discover stores, request bookings, and complete orders.
        Unless explicitly stated otherwise, Rentkia is not the owner of listed items and is not a party to the rental contract between a customer and a vendor.
    </p>

    <h2>2. Eligibility</h2>
    <p>
        You must be at least 18 years old and capable of entering a binding contract to use Rentkia.
        Vendors must provide accurate business information and maintain authority to offer listed items for rent.
    </p>

    <h2>3. Accounts</h2>
    <ul>
        <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
        <li>You agree to provide accurate, current, and complete account information.</li>
        <li>You are responsible for all activity that occurs under your account.</li>
        <li>We may suspend or terminate accounts that violate these Terms or applicable law.</li>
    </ul>

    <h2>4. Vendor listings and storefronts</h2>
    <p>
        Vendors are solely responsible for the accuracy of listings, pricing, availability, item condition, delivery or pickup terms, security deposits, and compliance with local regulations.
        Vendors must honour confirmed bookings except where cancellation is permitted by their stated policies or required by law.
    </p>

    <h2>5. Bookings, payments, and fees</h2>
    <ul>
        <li>Rental prices, taxes, delivery charges, deposits, and payment terms are set by vendors unless otherwise displayed at checkout.</li>
        <li>Payment processing may be handled through third-party providers subject to their terms.</li>
        <li>Subscription or platform fees for vendors, where applicable, are described at the time of purchase.</li>
        <li>Disputes regarding item quality, late returns, damage, or refunds should first be addressed with the relevant vendor.</li>
    </ul>

    <h2>6. Customer responsibilities</h2>
    <p>Customers agree to:</p>
    <ul>
        <li>Use rented items only for lawful purposes and in accordance with vendor instructions.</li>
        <li>Return items on time and in the condition received, ordinary wear excepted.</li>
        <li>Pay applicable rental charges, deposits, late fees, or damage assessments when due.</li>
        <li>Provide accurate contact and delivery information for fulfilment.</li>
    </ul>

    <h2>7. Prohibited conduct</h2>
    <p>You may not:</p>
    <ul>
        <li>Use Rentkia for fraudulent, misleading, or unlawful activity.</li>
        <li>Interfere with platform security, scrape data without permission, or attempt unauthorized access.</li>
        <li>Post offensive, infringing, or false content.</li>
        <li>Circumvent platform fees or solicit off-platform transactions in bad faith where prohibited.</li>
    </ul>

    <h2>8. Intellectual property</h2>
    <p>
        Rentkia and its logos, software, design, and content are owned by Rentkia or its licensors.
        Vendors retain ownership of their store content but grant Rentkia a license to display it on the platform for operational purposes.
    </p>

    <h2>9. Disclaimers</h2>
    <p>
        Rentkia is provided on an &ldquo;as is&rdquo; and &ldquo;as available&rdquo; basis.
        We do not warrant uninterrupted access, error-free operation, or that every listing is accurate or available.
        To the fullest extent permitted by law, we disclaim warranties arising from rentals between users and vendors.
    </p>

    <h2>10. Limitation of liability</h2>
    <p>
        To the maximum extent permitted by applicable law, Rentkia shall not be liable for indirect, incidental, special, consequential, or punitive damages,
        or for loss of profits, data, or goodwill arising from your use of the platform or any rental transaction.
    </p>

    <h2>11. Indemnity</h2>
    <p>
        You agree to indemnify and hold harmless Rentkia from claims, losses, and expenses arising from your use of the platform,
        your listings or bookings, your violation of these Terms, or your violation of any third-party rights.
    </p>

    <h2>12. Governing law</h2>
    <p>
        These Terms are governed by the laws of India, without regard to conflict-of-law principles.
        Courts located in Mumbai, Maharashtra shall have exclusive jurisdiction, subject to applicable consumer protection laws.
    </p>

    <h2>13. Changes</h2>
    <p>
        We may modify these Terms at any time by posting an updated version on this page.
        Material changes may also be communicated through the platform.
        Continued use after updates constitutes acceptance.
    </p>

    <h2>14. Contact</h2>
    <p>
        For questions about these Terms, contact:
        <a href="mailto:hello@rentkia.com">hello@rentkia.com</a>
    </p>
@endsection
