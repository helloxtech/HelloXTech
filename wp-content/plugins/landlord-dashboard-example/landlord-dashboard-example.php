<?php
/**
 * Plugin Name: Landlord Dashboard by Email
 * Description: Shows a detailed property dashboard for landlord users, keyed by the landlord's email address.
 * Version: 1.0
 * Author: Your Name
 */

/**
 * Registers the 'landlord' role on plugin activation.
 */
function ld_register_landlord_role()
{
    add_role(
        'landlord',
        'Landlord',
        [
            'read' => true
        ]
    );
}
register_activation_hook(__FILE__, 'ld_register_landlord_role');

/**
 * Removes the 'landlord' role on plugin deactivation.
 */
function ld_remove_landlord_role()
{
    remove_role('landlord');
}
register_deactivation_hook(__FILE__, 'ld_remove_landlord_role');

/**
 * Retrieves property data keyed by email address.
 * @param string $email Landlord's email.
 * @return array[] Array of property records.
 */
function ld_get_landlord_detailed_data_by_email($email)
{
    // Hard-coded mock data for demonstration.
    // The array key must match the user's email exactly.
    $sampleData = [
        'hurryupgo@gmail.com' => [
            [
                'property_name' => 'Downtown Loft',
                'location' => '123 Main St',
                'tenant_info' => 'Tenant: John Doe, Phone: (555) 111-2222',
                'lease_start' => '2025-01-01',
                'lease_end' => '2025-12-31',
                'next_rent_date' => '2025-04-01',
                'rent_history' => [
                    ['date' => '2025-02-01', 'amount' => 2500, 'status' => 'Paid'],
                    ['date' => '2025-03-01', 'amount' => 2500, 'status' => 'Paid']
                ],
                'maintenance_records' => [
                    ['date' => '2025-01-15', 'issue' => 'Heater Repair', 'cost' => 200],
                    ['date' => '2025-02-20', 'issue' => 'Plumbing Leak', 'cost' => 150]
                ],
                'scheduled_maintenance' => [
                    ['date' => '2025-04-10', 'description' => 'AC Checkup'],
                    ['date' => '2025-06-05', 'description' => 'Exterior Painting']
                ]
            ],
            [
                'property_name' => 'Sunset Villas',
                'location' => '20 Beach Rd',
                'tenant_info' => 'Tenant: Jane Smith, Phone: (555) 333-4444',
                'lease_start' => '2025-02-01',
                'lease_end' => '2026-01-31',
                'next_rent_date' => '2025-04-01',
                'rent_history' => [
                    ['date' => '2025-02-01', 'amount' => 3000, 'status' => 'Paid'],
                    ['date' => '2025-03-01', 'amount' => 3000, 'status' => 'Paid']
                ],
                'maintenance_records' => [
                    ['date' => '2025-02-25', 'issue' => 'Electrical Wiring Fix', 'cost' => 100]
                ],
                'scheduled_maintenance' => [
                    ['date' => '2025-05-01', 'description' => 'Garden Landscaping']
                ]
            ]
        ],
        // Feel free to add more email => property-data sets here.
        // 'another.landlord@example.com' => [...]
    ];

    return isset($sampleData[$email]) ? $sampleData[$email] : [];
}

/**
 * Renders the Landlord Dashboard via shortcode.
 * @return string HTML output.
 * Usage: [landlord_dashboard]
 */
function ld_landlord_dashboard_shortcode()
{
    if (!is_user_logged_in())
    {
        return 'Please log in to view your dashboard.';
    }

    $currentUser = wp_get_current_user();
    if (!in_array('landlord', $currentUser->roles))
    {
        return 'You do not have permission to view the landlord dashboard.';
    }

    // Get the landlord's email, which is used as the key in the sample data array.
    $landlordEmail = $currentUser->user_email;
    $properties = ld_get_landlord_detailed_data_by_email($landlordEmail);

    if (empty($properties))
    {
        return 'No property data found.';
    }

    // Generate HTML
    $html = '<div style="max-width: 900px; margin: 0 auto; font-family: Arial, sans-serif;">';
    $html .= '<h2 style="text-align:center;">Landlord Dashboard</h2>';

    foreach ($properties as $property)
    {
        $html .= '<div style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">';

        $html .= '<h3 style="margin-top:0;">' . esc_html($property['property_name']) . '</h3>';
        $html .= '<p><strong>Location:</strong> ' . esc_html($property['location']) . '</p>';
        $html .= '<p><strong>Tenant Info:</strong> ' . esc_html($property['tenant_info']) . '</p>';
        $html .= '<p><strong>Lease Period:</strong> ' . esc_html($property['lease_start'])
            . ' to ' . esc_html($property['lease_end']) . '</p>';
        $html .= '<p><strong>Next Rent Due:</strong> ' . esc_html($property['next_rent_date']) . '</p>';

        // Rent Payment History
        $html .= '<h4>Rent Payment History</h4>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%; margin-bottom:15px;">';
        $html .= '<tr style="background:#f0f0f0;"><th>Date</th><th>Amount</th><th>Status</th></tr>';

        if (!empty($property['rent_history']))
        {
            foreach ($property['rent_history'] as $payment)
            {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($payment['date']) . '</td>';
                $html .= '<td>$' . esc_html($payment['amount']) . '</td>';
                $html .= '<td>' . esc_html($payment['status']) . '</td>';
                $html .= '</tr>';
            }
        }
        else
        {
            $html .= '<tr><td colspan="3">No rent payments found.</td></tr>';
        }
        $html .= '</table>';

        // Maintenance Records
        $html .= '<h4>Maintenance & Repairs History</h4>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%; margin-bottom:15px;">';
        $html .= '<tr style="background:#f0f0f0;"><th>Date</th><th>Issue</th><th>Cost</th></tr>';

        if (!empty($property['maintenance_records']))
        {
            foreach ($property['maintenance_records'] as $record)
            {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($record['date']) . '</td>';
                $html .= '<td>' . esc_html($record['issue']) . '</td>';
                $html .= '<td>$' . esc_html($record['cost']) . '</td>';
                $html .= '</tr>';
            }
        }
        else
        {
            $html .= '<tr><td colspan="3">No maintenance records found.</td></tr>';
        }
        $html .= '</table>';

        // Scheduled Maintenance
        $html .= '<h4>Scheduled Maintenance</h4>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%;">';
        $html .= '<tr style="background:#f0f0f0;"><th>Date</th><th>Description</th></tr>';

        if (!empty($property['scheduled_maintenance']))
        {
            foreach ($property['scheduled_maintenance'] as $upcoming)
            {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($upcoming['date']) . '</td>';
                $html .= '<td>' . esc_html($upcoming['description']) . '</td>';
                $html .= '</tr>';
            }
        }
        else
        {
            $html .= '<tr><td colspan="2">No scheduled maintenance found.</td></tr>';
        }
        $html .= '</table>';

        $html .= '</div>'; // end property container
    }

    $html .= '</div>'; // end main container

    return $html;
}
add_shortcode('landlord_dashboard', 'ld_landlord_dashboard_shortcode');
