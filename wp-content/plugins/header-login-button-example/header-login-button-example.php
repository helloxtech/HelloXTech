<?php
/**
 * Plugin Name: Header Login Button Example
 * Description: Shows a button in the header via shortcode [header_login_button]. 
 *              If user not logged in, shows "Login" and redirects to /my-profile after login. 
 *              If logged in, shows user's email, linking to /my-profile.
 */

/**
 * Renders a button in the header. If not logged in, button reads "Login" 
 * and logs user into WP, then redirects to "/my-profile". 
 * If logged in, button shows the user's email and links to "/my-profile".
 * @return string HTML markup for the button.
 * Usage: [header_login_button]
 */
function hlb_login_button_shortcode()
{
    $profileUrl = site_url('/my-profile'); 
    // If "my-profile" page doesn't exist, adjust the slug accordingly.

    if (!is_user_logged_in())
    {
        // Pass the redirect URL to wp_login_url(), so user lands on "/my-profile" after login
        $loginUrl = wp_login_url($profileUrl);
        return '<button onclick="window.location=\'' . esc_url($loginUrl) . '\'">Login</button>';
    }
    else
    {
        // Logged in => show user's email, link to "/my-profile" page
        $currentUser = wp_get_current_user();
        $userEmail = esc_html($currentUser->user_email);

        return '<button onclick="window.location=\'' . esc_url($profileUrl) . '\'">'
             . $userEmail
             . '</button>';
    }
}
add_shortcode('header_login_button', 'hlb_login_button_shortcode');
