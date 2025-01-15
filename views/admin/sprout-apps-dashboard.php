<div id="si_dashboard" class="sprout_apps_dash wrap about-wrap">

	<img class="header_sa_logo" src="<?php echo esc_url( HSD_RESOURCES . 'admin/icons/sproutapps.png' ); ?>" />

	<h1>
		<?php
			printf(
				// translators: 1: opening anchor tag, 2: closing anchor tag, 3: plugin url 4: closing anchor tag.
				esc_html__( '%1$s%2$s%3$sSprout Apps%4$s thanks you!', 'help-scout' ),
				'<a href="',
				esc_url( self::PLUGIN_URL ),
				'">',
				'</a>',
			);
		?>
	</h1>

	<div class="about-text"><?php esc_html_e( 'Much thanks to Help Scout for partnering in this creation of this plugin, hopefully it helps your enjoyment of Help Scout. Our mission at Sprout Apps is to build a suite of apps/plugins to help small businesses and freelancers work more efficiently by reducing the tedious business tasks associated with client management. ', 'help-scout' ); ?></div>

	<div id="welcome-panel" class="welcome-panel clearfix">
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'Sprout Apps News and Updates', 'help-scout' ); ?></h2>
			<?php
				$maxitems = 0;
				include_once( ABSPATH . WPINC . '/feed.php' );
				$rss = fetch_feed( self::PLUGIN_URL.'/feed/' ); // FUTURE use feedburner
			if ( ! is_wp_error( $rss ) ) :
				$maxitems = $rss->get_item_quantity( 3 );
				$rss_items = $rss->get_items( 0, $maxitems );
				endif;
			?>
			<div class="rss_widget clearfix">
				<?php if ( $maxitems == 0 ) : ?>
					<p><?php esc_html_e( 'Could not connect to SIserver for updates.', 'help-scout' ); ?></p>
				<?php else : ?>
					<?php foreach ( $rss_items as $item ) :
						$excerpt = sa_get_truncate( wp_strip_all_tags( $item->get_content() ), 30 );
						?>
						<div>
							<h4><a href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo esc_attr( $item->get_title() ); ?>"><?php echo esc_html( wp_strip_all_tags( $item->get_title() ) ); ?></a></h4>
							<span class="rss_date"><?php echo esc_html( wp_strip_all_tags( $item->get_date( 'j F Y' ) ) ); ?></span>
							<p><?php echo wp_kses_post( $excerpt ); ?></p>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<a class="twitter-timeline" href="https://twitter.com/_sproutapps" data-widget-id="492426361349234688">Tweets by @_sproutapps</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>
	</div>

</div>

