<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(to right, #7367f0, #7367f0);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .content {
            background: #ffffff;
            padding: 30px 25px;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 10px 10px;
        }

        .content h2 {
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 16px;
            font-size: 16px;
        }

        .booking-details {
            background-color: #fdf2f7;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #f8d7da;
        }

        .booking-details h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .highlight {
            color: #7367f0;
            font-weight: 600;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #eaeaea;
        }

        .footer p {
            margin-bottom: 10px;
        }

        /* Star Rating Styles */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
            margin-right: 5px;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }

        .rating-section {
            margin: 10px 0;
        }

        .sale-details-table {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #e0e0e0;
        }

        .sale-details-table h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .sale-details-table table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .sale-details-table th,
        .sale-details-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        .sale-details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .sale-details-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .sale-details-table tr:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ {{ $companyName }} Sale Confirmation 🛍️</h1>
        </div>

        <div class="content">
            <h2>Dear {{ $customerName }},</h2>

            <p>Thank you for shopping with <strong>{{ $companyName }}</strong>! We’re thrilled to confirm your recent purchase.</p>

            <div class="sale-details">
                <h3>Your Order Details:</h3>
                <p>🏠 <strong>Branch:</strong> <span class="highlight">{{ $branchName }}</span></p>
                <p>📅 <strong>Sale Date:</strong> <span class="highlight">{{ $date }}</span></p>
                <p>🔖 <strong>Sale Reference:</strong> <span class="highlight">{{ $referenceNo }}</span></p>
                <p>💳 <strong>Payment Status:</strong> <span class="highlight">{{ $status }}</span></p>
                <p>💰 <strong>Total Amount:</strong> <span class="highlight">{{ $totalPayable }}</span></p>
                <p>💰 <strong>Total Paid:</strong> <span class="highlight">{{ $totalPaid }}</span></p>
                <p>💰 <strong>Total Due:</strong> <span class="highlight">{{ $totalDue }}</span></p>
            </div>

            <!-- Sale Details Table -->
            <div class="sale-details-table">
                <h3>Service Details:</h3>
                @php
                    // Fetch sale details with items using query builder
                    $saleDetails = DB::table('sale_details')
                        ->join('items', 'sale_details.item_id', '=', 'items.id')
                        ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                        ->where('sales.reference_no', $referenceNo)
                        ->where('sale_details.del_status', 'Live')
                        ->where('items.del_status', 'Live')
                        ->where('items.type', 'Service')
                        ->select(
                            'sale_details.id as sale_detail_id',
                            'sale_details.sale_id',
                            'items.id as item_id',
                            'items.name as item_name',
                            'items.type as item_type'
                        )
                        ->get();
                    
                    $saleId = DB::table('sales')
                        ->where('reference_no', $referenceNo)
                        ->where('del_status', 'Live')
                        ->value('id');
                        
                    $customerId = DB::table('sales')
                        ->where('reference_no', $referenceNo)
                        ->where('del_status', 'Live')
                        ->value('customer_id');
                @endphp
                
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">SN</th>
                            <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">Service Name</th>
                            <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($saleDetails) > 0)
                            @foreach($saleDetails as $index => $detail)
                                <tr>
                                    <td style="border: 1px solid #dee2e6; padding: 12px;">{{ $index + 1 }}</td>
                                    <td style="border: 1px solid #dee2e6; padding: 12px;">{{ $detail->item_name }}</td>
                                    <td style="border: 1px solid #dee2e6; padding: 12px;">
                                        <div class="rating-section" data-sale-detail-id="{{ $detail->sale_detail_id }}" data-item-id="{{ $detail->item_id }}">
                                            <div class="star-rating">
                                                <input type="radio" name="rating_{{ $detail->sale_detail_id }}" value="5" id="star5_{{ $detail->sale_detail_id }}">
                                                <label for="star5_{{ $detail->sale_detail_id }}">★</label>
                                                <input type="radio" name="rating_{{ $detail->sale_detail_id }}" value="4" id="star4_{{ $detail->sale_detail_id }}">
                                                <label for="star4_{{ $detail->sale_detail_id }}">★</label>
                                                <input type="radio" name="rating_{{ $detail->sale_detail_id }}" value="3" id="star3_{{ $detail->sale_detail_id }}">
                                                <label for="star3_{{ $detail->sale_detail_id }}">★</label>
                                                <input type="radio" name="rating_{{ $detail->sale_detail_id }}" value="2" id="star2_{{ $detail->sale_detail_id }}">
                                                <label for="star2_{{ $detail->sale_detail_id }}">★</label>
                                                <input type="radio" name="rating_{{ $detail->sale_detail_id }}" value="1" id="star1_{{ $detail->sale_detail_id }}">
                                                <label for="star1_{{ $detail->sale_detail_id }}">★</label>
                                            </div>
                                            <textarea name="comment_{{ $detail->sale_detail_id }}" placeholder="Write your comment here..." style="width: 100%; margin-top: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; min-height: 60px;"></textarea>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #6c757d;">No service items found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                
                @if(count($saleDetails) > 0)
                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" id="submitRatings" style="background-color: #7367f0; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500;">
                        Submit Ratings
                    </button>
                </div>
                @endif
            </div>

            <div class="footer">
                <p>🎉 Thank you for your purchase! 🎉</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submitButton = document.getElementById('submitRatings');
            
            if (submitButton) {
                submitButton.addEventListener('click', function() {
                    const ratings = [];
                    const ratingSections = document.querySelectorAll('.rating-section');
                    
                    ratingSections.forEach(function(section) {
                        const saleDetailId = section.getAttribute('data-sale-detail-id');
                        const itemId = section.getAttribute('data-item-id');
                        const ratingInput = section.querySelector('input[name^="rating"]:checked');
                        const commentTextarea = section.querySelector('textarea[name^="comment"]');
                        
                        if (ratingInput) {
                            ratings.push({
                                sale_detail_id: saleDetailId,
                                item_id: itemId,
                                rating: ratingInput.value,
                                comment: commentTextarea ? commentTextarea.value : ''
                            });
                        }
                    });
                    
                    if (ratings.length === 0) {
                        alert('Please select at least one rating before submitting.');
                        return;
                    }
                    
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.textContent = 'Submitting...';
                    
                    // Submit ratings
                    fetch('/api/submit-service-ratings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            ratings: ratings,
                            sale_id: '{{ $saleId }}',
                            customer_id: '{{ $customerId }}'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Thank you for your feedback! Your ratings have been submitted successfully.');
                            // Optionally hide the rating form or show success message
                            document.querySelector('.sale-details-table').innerHTML = '<div style="text-align: center; padding: 20px; color: #28a745;"><h3>Thank you for your feedback!</h3><p>Your ratings have been submitted successfully.</p></div>';
                        } else {
                            alert('Error submitting ratings: ' + (data.message || 'Please try again.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error submitting ratings. Please try again.');
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Submit Ratings';
                    });
                });
            }
        });
    </script>
</body>
</html>
