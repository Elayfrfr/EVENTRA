<?php
/**
 * Terms text. Wording from the Figma prototype (sections 4-11) plus sections 1-3 that were missing.
 * To rename the brand in the text, change $TERMS_BRAND below.
 */
$TERMS_BRAND = 'Bokla';
$TERMS_VERSION = $TERMS_BRAND . ' v1.0';
$TERMS = [
    ['1. Acceptance of Terms', 'By creating an account or using ' . $TERMS_BRAND . ', you agree to these Terms. If you do not agree, please do not use the platform.'],
    ['2. Eligibility and Accounts', $TERMS_BRAND . ' is for students and faculty of the university. You must use your real name and school ID when you sign up.'],
    ['3. Account Security', 'You are responsible for keeping your password safe and for everything done under your account. Tell us right away if you think someone else has used it.'],
    ['4. Creating Events', 'Users who create events must provide accurate details (title, date, time, venue, and description). Events must follow university policies. ' . $TERMS_BRAND . ' may remove events that are misleading, inappropriate, or unlawful.'],
    ['5. Registration and Tickets', 'Registering for an event generates a digital ticket tied to your account. Tickets are non transferable unless the event organizer allows it. Organizers may approve, limit, or cancel registrations based on capacity.'],
    ['6. Cancellations and Changes', 'Organizers may edit, reschedule, or cancel events. ' . $TERMS_BRAND . ' will notify registered attendees of any changes where possible.'],
    ['7. Acceptable Use', 'You agree not to post false information, harass others, misuse tickets, or attempt to disrupt or hack the platform.'],
    ['8. Privacy and Data', 'We collect your name, email, and registration activity to run the service. We do not sell your personal information. Your data is shared only with event organizers for events you register for.'],
    ['9. Limitation of Liability', $TERMS_BRAND . ' is a platform for listing and registering for events. We are not responsible for the conduct of organizers or attendees, or for changes made to events.'],
    ['10. Changes to Terms', 'We may update these Terms from time to time. Continued use of ' . $TERMS_BRAND . ' means you accept the updated Terms.'],
    ['11. Contact Us', 'Questions? Email us at ' . SUPPORT_EMAIL],
];
/** Echo the terms in two columns. */
function render_terms_columns(array $terms): void
{
    $half = (int)ceil(count($terms) / 2);
    echo '<div class="terms-cols">';
    foreach ([array_slice($terms, 0, $half), array_slice($terms, $half)] as $col) {
        echo '<div>';
        foreach ($col as $t) echo '<h4>' . e($t[0]) . '</h4><p>' . e($t[1]) . '</p>';
        echo '</div>';
    }
    echo '</div>';
}
