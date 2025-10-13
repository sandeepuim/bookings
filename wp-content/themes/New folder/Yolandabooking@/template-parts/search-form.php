<?php
/**
 * Template part for displaying the hotel search form
 */
?>

<div class="search-fields destination-fields">
    <div class="search-field">
        <label for="country">Country</label>
        <select name="country" id="country" required>
            <option value="">Select Country</option>
            <option value="IN">India</option>
            <option value="TH">Thailand</option>
            <option value="SG">Singapore</option>
            <option value="MY">Malaysia</option>
            <option value="ID">Indonesia</option>
            <option value="VN">Vietnam</option>
        </select>
    </div>

    <div class="search-field">
        <label for="city_code">City</label>
        <select name="city_code" id="city_code" required disabled>
            <option value="">Select City</option>
        </select>
    </div>

    <div class="search-field">
        <label for="rooms">Rooms</label>
        <select name="rooms" id="rooms">
            <option value="1" selected>1 Room</option>
            <option value="2">2 Rooms</option>
            <option value="3">3 Rooms</option>
            <option value="4">4 Rooms</option>
            <option value="5">5 Rooms</option>
        </select>
    </div>
</div>

<div class="search-fields">
    <div class="search-field">
        <label for="check_in">Check In</label>
        <input type="date" name="check_in" id="check_in" required
               min="<?php echo date('Y-m-d'); ?>"
               value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
    </div>

    <div class="search-field">
        <label for="adults">Adults</label>
        <select name="adults" id="adults">
            <option value="1">1 Adult</option>
            <option value="2" selected>2 Adults</option>
            <?php for($i = 3; $i <= 10; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> Adults</option>
            <?php endfor; ?>
        </select>
    </div>
</div>

<div class="search-fields">
    <div class="search-field">
        <label for="check_out">Check Out</label>
        <input type="date" name="check_out" id="check_out" required
               min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>"
               value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
    </div>

    <div class="search-field">
        <label for="children">Children</label>
        <select name="children" id="children">
            <option value="0" selected>0 Children</option>
            <?php for($i = 1; $i <= 6; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> Child<?php echo $i != 1 ? 'ren' : ''; ?></option>
            <?php endfor; ?>
        </select>
    </div>
</div>

<div class="search-button">
    <button type="submit" class="search-btn">Search Hotels</button>
</div>
