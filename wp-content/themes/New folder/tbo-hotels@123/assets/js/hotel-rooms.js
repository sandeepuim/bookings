/**
 * TBO Hotels - Room Selection JavaScript
 * Handles the hotel room selection functionality
 */

jQuery(document).ready(function($) {
    // Handle select room button clicks
    $('.select-room-btn').on('click', function() {
        var roomIndex = $(this).data('room-index');
        var roomName = $('#room-' + roomIndex + ' .room-name').text();
        
        // Show selection confirmation
        showRoomSelectionModal(roomIndex, roomName);
    });
    
    // Handle modify search button click
    $('.modify-search-btn').on('click', function() {
        // Redirect back to search page with current parameters
        var currentUrl = window.location.href;
        var searchParams = new URLSearchParams(window.location.search);
        
        // Redirect to the search page with current parameters
        window.location.href = '/bookings/hotel-search/?' + searchParams.toString();
    });
    
    /**
     * Show room selection confirmation modal
     */
    function showRoomSelectionModal(roomIndex, roomName) {
        // Create modal HTML
        var modalHtml = `
            <div class="room-selection-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Room Selected</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="selection-icon">✓</div>
                        <h4>You've selected:</h4>
                        <p class="selected-room-name">${roomName}</p>
                        <div class="next-steps">
                            <p>Ready to proceed with your booking?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="continue-booking-btn">Continue to Booking</button>
                        <button class="cancel-selection-btn">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        // Append modal to body
        $('body').append(modalHtml);
        
        // Add modal styles
        $('<style>')
            .text(`
                .room-selection-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                }
                .modal-content {
                    background-color: #fff;
                    border-radius: 8px;
                    width: 100%;
                    max-width: 500px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                    animation: modalFadeIn 0.3s;
                }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(-50px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .modal-header {
                    padding: 15px 20px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .modal-header h3 {
                    margin: 0;
                    color: #2c3e50;
                }
                .close-modal {
                    font-size: 24px;
                    cursor: pointer;
                    color: #7f8c8d;
                }
                .modal-body {
                    padding: 30px 20px;
                    text-align: center;
                }
                .selection-icon {
                    background-color: #2ecc71;
                    color: white;
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-size: 30px;
                    margin: 0 auto 20px;
                }
                .modal-body h4 {
                    margin-top: 0;
                    margin-bottom: 10px;
                    color: #2c3e50;
                }
                .selected-room-name {
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 20px;
                    color: #e74c3c;
                }
                .next-steps {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .next-steps p {
                    margin: 0;
                    color: #34495e;
                }
                .modal-footer {
                    padding: 15px 20px;
                    border-top: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                }
                .continue-booking-btn {
                    background-color: #e74c3c;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .continue-booking-btn:hover {
                    background-color: #c0392b;
                }
                .cancel-selection-btn {
                    background-color: #ecf0f1;
                    color: #7f8c8d;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .cancel-selection-btn:hover {
                    background-color: #dfe4ea;
                }
            `)
            .appendTo('head');
        
        // Handle close modal
        $('.close-modal, .cancel-selection-btn').on('click', function() {
            $('.room-selection-modal').remove();
        });
        
        // Handle continue to booking
        $('.continue-booking-btn').on('click', function() {
            // Here you would implement the booking functionality
            // For now, just show an alert
            alert('Booking functionality would be implemented here. Selected room: ' + roomName);
            $('.room-selection-modal').remove();
        });
    }
});