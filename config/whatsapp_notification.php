<?php
/**
 * WhatsApp Notification Helper
 * Creates a direct WhatsApp link for the user to receive messages
 * This method doesn't send messages automatically but provides a clickable link
 */

function getWhatsAppLink($phone, $message) {
    // Format phone number (remove any non-numeric characters)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present (assuming India +91 by default)
    if (strlen($phone) === 10) {
        $phone = '91' . $phone;
    }
    
    // Encode the message for URL
    $encodedMessage = urlencode($message);
    
    // Create WhatsApp direct link
    $whatsappLink = "https://wa.me/{$phone}?text={$encodedMessage}";
    
    return $whatsappLink;
}

/**
 * Sends a WhatsApp notification by returning a clickable link
 * Returns the link that the user can click to open WhatsApp with the pre-filled message
 */
function sendWhatsAppNotification($phone, $message) {
    return getWhatsAppLink($phone, $message);
}

/**
 * Gets the user's WhatsApp link for order confirmation
 */
function getOrderConfirmationLink($userId, $orderDetails) {
    global $db;
    
    // Get user's phone number from database
    $stmt = $db->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || empty($user['phone'])) {
        return false;
    }
    
    // Create the message
    $message = "🎉 *Order Confirmed!* 🎉\n\n";
    $message .= "Hello! Your order has been confirmed.\n\n";
    $message .= "*Order Details:*\n";
    $message .= "Plan: {$orderDetails['plan_name']}\n";
    $message .= "Duration: {$orderDetails['start_date']} to {$orderDetails['end_date']}\n";
    $message .= "Amount: ₹{$orderDetails['amount']}\n\n";
    $message .= "Thank you for choosing Tiffinly!\n";
    $message .= "For any queries, contact us at +91 1234567890.";
    
    // Return the WhatsApp link
    return getWhatsAppLink($user['phone'], $message);
}

/**
 * Format phone number for WhatsApp API
 * Removes all non-numeric characters and ensures it starts with country code (91 for India)
 */
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If number starts with 0, remove it
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    
    // If number doesn't start with country code (91 for India), add it
    if (substr($phone, 0, 2) !== '91' && strlen($phone) === 10) {
        $phone = '91' . $phone;
    }
    
    return $phone;
}

/**
 * Send subscription confirmation message
 */
function sendSubscriptionConfirmation($userPhone, $subscriptionDetails) {
    $formattedPhone = formatPhoneNumber($userPhone);
    
    $message = "🎉 *Subscription Confirmed!* 🎉\n\n";
    $message .= "*Plan:* {$subscriptionDetails['plan_name']}\n";
    $message .= "*Duration:* {$subscriptionDetails['start_date']} to {$subscriptionDetails['end_date']}\n";
    $message .= "*Schedule:* {$subscriptionDetails['schedule']}\n";
    $message .= "*Delivery Time:* {$subscriptionDetails['delivery_time']}\n";
    $message .= "*Amount Paid:* ₹{$subscriptionDetails['amount']}\n\n";
    $message .= "Thank you for subscribing to Tiffinly! Your meals will be delivered as per schedule.\n\n";
    $message .= "For any queries, please contact us at +91 1234567890 or support@tiffinly.com";
    
    return sendWhatsAppNotification($formattedPhone, $message);
}
