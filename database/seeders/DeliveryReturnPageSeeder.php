<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageSection;

class DeliveryReturnPageSeeder extends Seeder
{
    public function run(): void
    {
        $deliveryIntroContent = <<<'HTML'
<h3>Delivery Coverage</h3>
<p>We deliver to all areas across the United Arab Emirates. Shipping is conducted by trusted third-party courier services to ensure safe and timely delivery of your orders.</p>
HTML;

        $deliveryProcessingContent = <<<'HTML'
<h3>Processing &amp; Tracking</h3>
<ul>
    <li>Orders are processed within 3-4 working hours after purchase completion</li>
    <li>A tracking number will be sent to your registered email address</li>
    <li>You can track your order status in real-time through our tracking portal</li>
    <li>Delivery takes 2-3 business days</li>
</ul>

<hr>

<h3>Delivery Times</h3>
<p>Delivery of orders will take 2-3 business days. Orders placed during weekends (Saturday - Sunday) or during any UAE public holidays might take longer than usual time to be processed.</p>
<blockquote>Our customer care team will be happy to assist you with any changes to your preferred delivery date and address. However, if the status of your order is dispatched, we will not be able to make any changes to your order.</blockquote>

<hr>

<h3>Pickup Option</h3>
<p>At pv.market, we understand that different customers have different payment preferences. If you prefer pickup from our showroom, there will be no shipping fee. You can pick up your product within 1 hour of order placement, subject to product availability at the showroom.</p>

<hr>

<h3>Delivery Delays</h3>
<p>Avoid any delivery delay by providing your full address along with your contact details. In case of any unforeseen circumstances or force majeure events, delivery times may be extended. Please note that in the event of a return, the refunded amount will be issued as a credit note that can be used on your next order. The shipping fee is non-refundable.</p>
HTML;

        $returnContent = <<<'HTML'
<h3>Return Eligibility</h3>
<p>If you're dissatisfied with your purchase, you can return the product; however, please note that the shipping fee, documentation charges, MOFA charges, and online payment transaction fees will not be refunded.</p>
<p>Items that are not in their original selling condition will not be accepted for return.</p>
<ul>
    <li>The products must be returned in the same condition in which they were received</li>
    <li>All products must be sealed, unused and in a perfectly saleable condition with all accessories</li>
    <li>Items must be returned with original box, instructions, and warranty card</li>
</ul>

<hr>

<h3>Return Window</h3>
<p>Confirmed orders shipped from pv.market and Orange Overseas can only be returned (cannot be exchanged) within <strong>7 working days</strong> of receipt of shipment. Once the items are received and inspected by us, we will issue a refund. The shipping and handling fees are non-refundable.</p>

<hr>

<h3>Damaged Goods</h3>
<p>If the product delivered is faulty or damaged, please issue a return request at <strong>info@pv.market</strong></p>
<blockquote><strong>Important:</strong> Please inspect your order upon delivery and contact us immediately if the item is defective, damaged, or if you receive the wrong item. We will evaluate the issue and make it right.</blockquote>

<hr>

<h3>Incorrectly Fulfilled Orders</h3>
<p>If you have received a product that you did not order, please initiate a return request at <strong>info@pv.market</strong>. We will arrange for a pickup and process a full refund or exchange.</p>

<hr>

<h3>Refund Timescale</h3>
<p>Your refund will be initiated once your product is received back in our warehouse or showroom and has been inspected by our team. Once the product inspection has been completed and the product has been found to be in <strong>ORIGINAL</strong> condition, we will process your refund.</p>

<hr>

<h3>Cancellation</h3>
<p>Please note that we cannot accept cancellations once the order is placed, and it can only be canceled by following the returns process mentioned below.</p>

<hr>

<h3>How to Return Your Order</h3>
<ol>
    <li>Write an email to <strong>info@pv.market</strong> with your Order number</li>
    <li>You will receive a confirmation email to inform you that your refund has been processed</li>
    <li>Please contact your payment provider or bank for more information about when your refund may be available to you</li>
</ol>
HTML;

        $sections = [

            // ── PAGE HEADER ────────────────────────────────
            [
                'page'      => 'delivery',
                'section'   => 'delivery_header',
                'type'      => 'text_block',
                'title'     => 'Delivery and Return Policy',
                'subtitle'  => 'Last updated: January 2025',
                'extra'     => ['content' => ''],
                'order'     => 1,
                'is_active' => true,
            ],

            // ── HIGHLIGHT CARDS ────────────────────────────
            [
                'page'      => 'delivery',
                'section'   => 'delivery_highlights',
                'type'      => 'cards',
                'title'     => '',
                'subtitle'  => '',
                'extra'     => [
                    'items' => [
                        [
                            'icon'  => '🚚',
                            'title' => 'Fast Delivery',
                            'desc'  => 'Orders processed within 3-4 working hours',
                        ],
                        [
                            'icon'  => '🔄',
                            'title' => 'Easy Returns',
                            'desc'  => '7 working days return window for eligible items',
                        ],
                        [
                            'icon'  => '🛡️',
                            'title' => 'Secure Process',
                            'desc'  => 'Refund issued after inspection for eligible returns',
                        ],
                    ],
                ],
                'order'     => 2,
                'is_active' => true,
            ],

            // ── DELIVERY INTRO ─────────────────────────────
            [
                'page'      => 'delivery',
                'section'   => 'delivery_intro',
                'type'      => 'richtext',
                'title'     => 'Delivery Policy',
                'subtitle'  => null,
                'extra'     => ['content' => $deliveryIntroContent],
                'order'     => 3,
                'is_active' => true,
            ],

            // ── SHIPPING FEES TABLE ────────────────────────
            // Stored as structured JSON so the admin panel can
            // render a proper grid of inputs instead of a raw
            // HTML table inside a WYSIWYG editor.
            [
                'page'      => 'delivery',
                'section'   => 'delivery_shipping_fees',
                'type'      => 'shipping_table',
                'title'     => 'Shipping Fees',
                'subtitle'  => 'A shipping fee will be applied during checkout and may vary based on the product\'s weight and the destination city for shipping.',
                'extra'     => [
                    'currency_note' => 'All prices are in AED',
                    'columns'       => [
                        'Dubai',
                        'Sharjah',
                        'Ajman / UAQ',
                        'FUJ / RAK',
                        'Abu Dhabi',
                    ],
                    'rows' => [
                        [
                            'vehicle' => '3 TON',
                            'prices'  => [250, 300, 350, 410, 410],
                        ],
                        [
                            'vehicle' => '7 TON',
                            'prices'  => [450, 550, 550, 850, 850],
                        ],
                        [
                            'vehicle' => '10 Ton & TRAILOR',
                            'prices'  => [650, 850, 900, 1200, 1200],
                        ],
                    ],
                ],
                'order'     => 4,
                'is_active' => true,
            ],

            // ── DELIVERY PROCESSING & REST OF POLICY ───────
            [
                'page'      => 'delivery',
                'section'   => 'delivery_processing',
                'type'      => 'richtext',
                'title'     => null,
                'subtitle'  => null,
                'extra'     => ['content' => $deliveryProcessingContent],
                'order'     => 5,
                'is_active' => true,
            ],

            // ── RETURN POLICY ──────────────────────────────
            [
                'page'      => 'delivery',
                'section'   => 'return_policy',
                'type'      => 'richtext',
                'title'     => 'Return Policy',
                'subtitle'  => null,
                'extra'     => ['content' => $returnContent],
                'order'     => 6,
                'is_active' => true,
            ],

            // ── NEED HELP ──────────────────────────────────
            [
                'page'      => 'delivery',
                'section'   => 'delivery_help',
                'type'      => 'text_block',
                'title'     => 'Need Help?',
                'subtitle'  => 'info@pv.market',
                'extra'     => [
                    'content' => 'If you have any questions about our delivery and return policy, please don\'t hesitate to contact us.',
                ],
                'order'     => 7,
                'is_active' => true,
            ],

        ];

        foreach ($sections as $data) {
            PageSection::updateOrCreate(
                ['page' => $data['page'], 'section' => $data['section']],
                $data
            );
        }

        $this->command->info('✅ Delivery & Return page seeded!');
    }
}
