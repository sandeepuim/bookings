<?php
/* Template Name: Front page */
get_header();
?>
<style>
    .hotel-search-section {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo get_template_directory_uri(); ?>/assets/images/hotel-banner.jpg');
        background-size: cover;
        background-position: center;
        padding: 80px 0;
        color: white;
    }
    
    .search-title {
        font-size: 36px;
        margin-bottom: 30px;
        text-align: center;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }
    
    .hotel-search-form {
        background: rgba(255,255,255,0.9);
        padding: 30px;
        border-radius: 8px;
        max-width: 960px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        color: #333;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
        text-align: center;
    }
    
    .search-btn {
        background: #0073aa;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .search-btn:hover {
        background: #005177;
    }
    
    /* Loading spinner for dropdown */
    .loading::after {
        content: "";
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-left: 10px;
        vertical-align: middle;
        border: 2px solid rgba(0,0,0,0.2);
        border-radius: 50%;
        border-top-color: #0073aa;
        animation: spinner 0.6s linear infinite;
    }
    
    @keyframes spinner {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<section class="hotel-search-section">
    <div class="container">
        <h2 class="search-title">Find the Best Hotels</h2>
        <form method="get" action="<?php echo site_url('/hotel-results'); ?>" class="hotel-search-form">

            <div class="form-group">
                <label for="country_code">Country:</label>
                <select name="country_code" id="country_code" required>
                    <option value="">Loading countries...</option>
                </select>
            </div>

            <div class="form-group">
                <label for="city_code">City:</label>
                <select name="city_code" id="city_code" required>
                    <option value="">Select a country first</option>
                </select>
            </div>

            <div class="form-group">
                <label for="check_in">Check In</label>
                <input type="date" name="check_in" id="check_in" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="check_out">Check Out</label>
                <input type="date" name="check_out" id="check_out" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="rooms">Rooms</label>
                <input type="number" name="rooms" id="rooms" value="1" min="1" max="5">
            </div>

            <div class="form-group">
                <label for="adults">Adults</label>
                <input type="number" name="adults" id="adults" value="2" min="1" max="10">
            </div>

            <div class="form-group">
                <label for="children">Children</label>
                <input type="number" name="children" id="children" value="0" min="0" max="6">
            </div>

            <div class="form-group full-width">
                <button type="submit" class="search-btn">Search Hotels</button>
            </div>
            
        </form>
    </div>
</section>
<!-- Container where AJAX search results will be rendered by assets/js/hotel-search.js -->
<div id="search-results" style="display: none; padding: 40px 0;"></div>

<?php get_footer(); ?>