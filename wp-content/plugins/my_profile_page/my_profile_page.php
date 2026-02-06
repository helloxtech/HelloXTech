<?php
/**
 * Plugin Name: My Profile Page Example
 * Description: Creates a vertical navigation with Profile and Change Password sections. [my_profile_page]
 * Version: 1.0
 * Author: Your Name
 */

/**
 * Returns a simple two-column layout:
 * Left: Vertical nav with "Profile" and "Change Password"
 * Right: Toggles between profile form and change-password form.
 * Usage: [my_profile_page]
 * @return string
 */
function mpp_my_profile_page_shortcode()
{
    ob_start();
    ?>
    <style>
    .mpp-container
    {
        display: flex; 
        max-width: 800px; 
        margin: 0 auto; 
        font-family: Arial, sans-serif;
    }
    .mpp-nav
    {
        width: 200px; 
        border-right: 1px solid #ccc; 
        padding-right: 10px;
    }
    .mpp-nav ul 
    {
        list-style: none; 
        padding: 0;
    }
    .mpp-nav li 
    {
        margin-bottom: 10px;
    }
    .mpp-nav a
    {
        cursor: pointer; 
        color: #0073aa; 
        text-decoration: none;
    }
    .mpp-content
    {
        flex-grow: 1; 
        padding-left: 20px;
    }
    .mpp-section
    {
        display: none;
    }
    .mpp-section.active
    {
        display: block;
    }
    .mpp-section h2
    {
        margin-top: 0;
    }
    .mpp-form label 
    {
        display: block; 
        margin: 5px 0 2px;
    }
    .mpp-form input 
    {
        margin-bottom: 10px; 
        width: 100%; 
        max-width: 300px;
    }
    </style>

    <div class="mpp-container">
        <div class="mpp-nav">
            <ul>
                <li><a data-target="profile" class="mpp-toggle-link">Profile</a></li>
                <li><a data-target="change-password" class="mpp-toggle-link">Change Password</a></li>
            </ul>
        </div>
        <div class="mpp-content">
            <div id="profile" class="mpp-section active">
                <h2>Profile</h2>
                <form class="mpp-form">
                    <label>Name</label>
                    <input type="text" value="John Doe" />
                    <label>Email</label>
                    <input type="email" value="john@example.com" />
                    <button type="button">Update</button>
                </form>
            </div>
            <div id="change-password" class="mpp-section">
                <h2>Change Password</h2>
                <form class="mpp-form">
                    <label>Current Password</label>
                    <input type="password" />
                    <label>New Password</label>
                    <input type="password" />
                    <label>Confirm New Password</label>
                    <input type="password" />
                    <button type="button">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function()
    {
        var links = document.querySelectorAll('.mpp-toggle-link');
        var sections = document.querySelectorAll('.mpp-section');

        links.forEach(function(link)
        {
            link.addEventListener('click', function()
            {
                var targetId = this.getAttribute('data-target');
                sections.forEach(function(sec)
                {
                    sec.classList.remove('active');
                });
                document.getElementById(targetId).classList.add('active');
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('my_profile_page', 'mpp_my_profile_page_shortcode');
