<div id="orbtr-dash-widget" class="orbtr-dashboard-header cf">
    <div class="dashboard-widget-stats cf">
        <div class="dash-now cf">
            <div class="orbtr-stats-column">
                <span class="orbtr-visits"><?php echo (int)$stats->visitors_now; ?></span>
                <span class="orbtr-stats-text">Total Visitors</span>
            </div>
            <div class="orbtr-stats-column">
                <span class="orbtr-visits"><?php echo (int)$stats->leads_now; ?></span>
                <span class="orbtr-stats-text">Leads</span>
            </div>
        </div>
    </div>
    <div class="dashboard-widget-stats cf">
        <div class="dash-now dash-today cf">
            <div class="orbtr-stats-column">
                <span class="orbtr-visits"><?php echo (int)$stats->visitors_today; ?></span>
                <span class="orbtr-stats-text">Total Visitors</span>
            </div>
            <div class="orbtr-stats-column">
                <span class="orbtr-visits"><?php echo (int)$stats->leads_today; ?></span>
                <span class="orbtr-stats-text">Leads</span>
            </div>
        </div>
    </div>
</div>
<div class="orbtr-dash-secondary cf">
    <p><span>New Visits:</span> <?php echo (float)$stats->new_percent; ?>%</p>
    <p><span>Total Pages:</span> <?php echo (int)$stats->total_pages; ?></p><br />
    <p><span>Total Visits:</span> <?php echo (int)$stats->total_visits; ?></p>
    <p><span>Avg Pages/Visit</span> <?php echo ((int)$stats->total_visits > 0) ? round(($stats->total_pages / $stats->total_visits), 1): 0; ?></p>
</div>
<div class="orbtr-dash-actions cf">
    <a href="admin.php?page=orbtrping">ORBTR Ping Dashboard</a> <a href="https://orbtr.freshdesk.com" target="_blank">Support Tickets</a>
</div>
<div class="orbtr-dash-news">
    <h3>ORBTR News &amp; Updates</h3>
    <?php // Get RSS Feed(s)
    include_once( ABSPATH . WPINC . '/feed.php' );
    
    // Get a SimplePie feed object from the specified feed source.
    $rss = fetch_feed( 'http://orbtr.net/?feed=rss2' );
    
    if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
    
        // Figure out how many total items there are, but limit it to 5. 
        $maxitems = $rss->get_item_quantity( 4 ); 
    
        // Build an array of all the items, starting with element 0 (first element).
        $rss_items = $rss->get_items( 0, $maxitems );
    //else:
        //die(print_r($rss, true));
    endif;
    ?>
    
    <ul>
        <?php if ( $maxitems == 0 ) : ?>
            <li><?php _e( 'No items', 'my-text-domain' ); ?></li>
        <?php else : ?>
            <?php // Loop through each feed item and display each item as a hyperlink. ?>
            <?php foreach ( $rss_items as $item ) : ?>
                <li>
                    <a href="<?php echo esc_url( $item->get_permalink() ); ?>" target="_blank">
                        <?php echo esc_html( $item->get_title() ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <p class="right"><a href="http://orbtr.net" target="_blank">Visit ORBTR.net</a></p>
</div> 