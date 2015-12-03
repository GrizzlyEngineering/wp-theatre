<?php
/*
 * Manages production listings.
 *
 * Uses this class to compile lists of productions or fully formatted HTML listings of productions.
 *
 * @since 	0.5
 * @since 	0.10	Complete rewrite, while maintaining backwards compatibility.
 * @since	0.14	Use WP_Query instead of get_posts().
 */

class WPT_Productions extends WPT_Listing {

	/**
	 * Adds the page selectors for seasons, categories, days, months and years to the public query vars.
	 *
	 * Necessary to make `$wp_query->query_vars['wpt_category']` work.
	 *
	 * @since 	0.10
	 * @since	0.13	Added new query vars for days, months and years.
	 *
	 * @param 	array 	$vars	The current public query vars.
	 * @return 	array			The new public query vars.
	 */
	public function add_query_vars($vars) {
		$vars[] = 'wpt_day';
		$vars[] = 'wpt_month';
		$vars[] = 'wpt_year';
		$vars[] = 'wpt_season';
		$vars[] = 'wpt_category';
		return $vars;
	}

	/**
	 * Gets all categories with productions.
	 *
	 * @since 	0.5
	 * @since 	0.10	Renamed method from `categories()` to `get_categories()`.
	 * @since 	0.10.2	Now returns the slug instead of the term_id as the array keys.
	 * @since 	0.10.14	Significally decreased the number of queries used.
	 * @since	0.13.3	Now uses the production filters.
	 *					Added filters to manipulate the categories.
	 *
	 * @param 	array 	$filters	See WPT_Productions::get() for possible values.
	 * @return 	array 				The categories.
	 */
	function get_categories($filters) {
		$categories = array();

		$productions = $this->get($filters);
		
		if (!empty($productions)) {
			$production_ids = wp_list_pluck( $productions, 'ID' );
	
			/**
			 * Filter the categories arguments.
			 * You can use this to alter the ordering of the categories.
			 * For possible values see: 
			 * https://codex.wordpress.org/Function_Reference/wp_get_object_terms
			 * 
			 * @since	0.13.3
			 * @param	$args	array	The current arguments.
			 */
			$args = apply_filters('wpt/productions/categories/args', array() );
			$terms = wp_get_object_terms( $production_ids, 'category', $args );
	
			foreach ( $terms as $term ) {
				$categories[ $term->slug ] = $term->name;
			}
	
			asort( $categories );
		}
		
		/**
		 * Filter the categories that have productions.
		 * 
		 * @since	0.13.3
		 * @param	array	$categories	The current categories.
		 * @param	array	$filters	The production filters.
		 */
		$categories = apply_filters('wpt/productions/categories', $categories, $filters);
		
		return $categories;
	}

	/**
	 * Gets the CSS classes for a production listing.
	 *
	 * @see WPT_Listing::get_classes_for_html()
	 *
	 * @since 0.10
	 *
	 * @access 	protected
	 * @param 	array $args 	See WPT_Productions::get_html() for possible values. Default: array().
	 * @return 	array 			The CSS classes.
	 */
	protected function get_classes_for_html($args = array()) {

		// Start with the default classes for listings.
		$classes = parent::get_classes_for_html();

		$classes[] = 'wpt_productions';

		// Thumbnail
		if ( ! empty($args['template']) && false === strpos( $args['template'],'{{thumbnail}}' ) ) {
			$classes[] = 'wpt_productions_without_thumbnail';
		}

		/**
		 * Filter the CSS classes.
		 *
		 * @since 0.10
		 *
		 * @param 	array $classes 	The CSS classes.
		 * @param 	array $args 	The $args that are being used for the listing.
		 */
		$classes = apply_filters( 'wpt_productions_classes', $classes, $args );

		return $classes;
	}

	/**
	 * Gets all days with productions.
	 *
	 * @since 0.13
	 *
	 * @param 	array $filters  The filters for the productions.
	 *							See WPT_Productions::get() for possible values.
	 * @return 	array 			The days.
	 */
	private function get_days($filters) {
		global $wp_theatre;

		$days = array();

		$production_ids = array();
		foreach ( $this->get( $filters ) as $production ) {
			$production_ids[] = $production->ID;
		}
		$production_ids = array_unique( $production_ids );

		if ( ! empty($production_ids) ) {
			// Inherit the date filters from the production filters.
			$event_defaults = array(
				'upcoming' => false,
				'start' => false,
				'end' => false,
				'production' => $production_ids,
			);
			$event_filters = shortcode_atts( $event_defaults, $filters );
			$days = $wp_theatre->events->get_days( $event_filters );
		}
		return $days;
	}

	/**
	 * Gets all months with productions.
	 *
	 * @since 0.13
	 *
	 * @param 	array $filters  The filters for the productions.
	 *							See WPT_Productions::get() for possible values.
	 * @return 	array 			The months.
	 */
	private function get_months($filters) {
		global $wp_theatre;

		$months = array();

		$production_ids = array();
		foreach ( $this->get( $filters ) as $production ) {
			$production_ids[] = $production->ID;
		}
		$production_ids = array_unique( $production_ids );

		if ( ! empty($production_ids) ) {
			// Inherit the date filters from the production filters.
			$event_defaults = array(
				'upcoming' => false,
				'start' => false,
				'end' => false,
				'production' => $production_ids,
			);
			$event_filters = shortcode_atts( $event_defaults, $filters );
			$months = $wp_theatre->events->get_months( $event_filters );
		}
		return $months;
	}

	/**
	 * Gets all years with productions.
	 *
	 * @since 0.13
	 *
	 * @param 	array $filters  The filters for the productions.
	 *							See WPT_Productions::get() for possible values.
	 * @return 	array 			The years.
	 */
	private function get_years($filters) {
		global $wp_theatre;

		$years = array();
		$production_ids = array();
		foreach ( $this->get( $filters ) as $production ) {
			$production_ids[] = $production->ID;
		}
		$production_ids = array_unique( $production_ids );

		if ( ! empty($production_ids) ) {
			// Inherit the date filters from the production filters.
			$event_defaults = array(
				'upcoming' => false,
				'start' => false,
				'end' => false,
				'production' => $production_ids,
			);
			$event_filters = shortcode_atts( $event_defaults, $filters );
			$years = $wp_theatre->events->get_years( $event_filters );
		}
		return $years;

	}

	/**
	 * Gets a list of productions in HTML for a single day.
	 *
	 * @since 0.13
	 *
	 * @see WPT_Productions::get_html_grouped();
	 *
	 * @access 	private
	 * @param 	string $day		The day in `YYYY-MM-DD` format.
	 * @param 	array $args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	private function get_html_for_day($day, $args = array()) {

		/*
		 * Set the `start`-filter to today.
		 * Except when the active `start`-filter is set to a later date.
		 */
		if (
			empty($args['start']) ||
			(strtotime( $args['start'] ) < strtotime( $day ))
		) {
			$args['start'] = $day;
		}

		/*
		 * Set the `end`-filter to the next day.
		 * Except when the active `end`-filter is set to an earlier date.
		 */
		if (
			empty($args['end']) ||
			(strtotime( $args['end'] ) > strtotime( $day.' +1 day' ))
		) {
			$args['end'] = $day.' +1 day';
		}

		// No sticky productions in a day view.
		$args['ignore_sticky_posts'] = true;

		return $this->get_html_grouped( $args );
	}

	/**
	 * Gets a list of productions in HTML for a single month.
	 *
	 * @since 0.13
	 *
	 * @see WPT_Productions::get_html_grouped();
	 *
	 * @access 	private
	 * @param 	string 	$month	The month in `YYYY-MM` format.
	 * @param 	array 	$args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	private function get_html_for_month($month, $args = array()) {

		/*
		 * Set the `start`-filter to the first day of the month.
		 * Except when the active `start`-filter is set to a later date.
		 */
		if (
			empty($args['start']) ||
			(strtotime( $args['start'] ) < strtotime( $month ))
		) {
			$args['start'] = $month;
		}

		/*
		 * Set the `end`-filter to the first day of the next month.
		 * Except when the active `end`-filter is set to an earlier date.
		 */
		if (
			empty($args['end']) ||
			(strtotime( $args['end'] ) > strtotime( $month.' +1 month' ))
		) {
			$args['end'] = $month.' +1 month';
		}

		// No sticky productions in a month view.
		$args['ignore_sticky_posts'] = true;

		return $this->get_html_grouped( $args );
	}

	/**
	 * Gets a list of productions in HTML for a single year.
	 *
	 * @since 0.13
	 *
	 * @see WPT_Productions::get_html_grouped();
	 *
	 * @access private
	 * @param 	string 	$year	The year in `YYYY` format.
	 * @param 	array 	$args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	private function get_html_for_year($year, $args = array()) {

		/*
		 * Set the `start`-filter to the first day of the year.
		 * Except when the active `start`-filter is set to a later date.
		 */
		if (
			empty($args['start']) ||
			(strtotime( $args['start'] ) < strtotime( $year.'-01-01' ))
		) {
			$args['start'] = $year.'-01-01';
		}

		/*
		 * Set the `end`-filter to the first day of the next year.
		 * Except when the active `end`-filter is set to an earlier date.
		 */
		if (
			empty($args['end']) ||
			(strtotime( $args['end'] ) > strtotime( $year.'-01-01 +1 year' ))
		) {
			$args['end'] = $year.'-01-01 +1 year';
		}

		// No sticky productions in a year view.
		$args['ignore_sticky_posts'] = true;

		return $this->get_html_grouped( $args );
	}

	/**
	 * Gets a list of productions in HTML for a page.
	 *
	 * @since 	0.10
	 * @since	0.13	Added support for days, months and years.
	 *
	 * @see WPT_Productions::get_html_grouped();
	 * @see WPT_Productions::get_html_for_season();
	 * @see WPT_Productions::get_html_for_category();
	 *
	 * @access protected
	 * @param 	array $args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	protected function get_html_for_page($args = array()) {
		global $wp_query;

		/*
		 * Check if the user used the page navigation to select a particular page.
		 * Then revert to the corresponding WPT_Events::get_html_for_* method.
		 * @see WPT_Events::get_html_page_navigation().
		 */

		if ( ! empty($wp_query->query_vars['wpt_season']) ) {
			$html = $this->get_html_for_season( $wp_query->query_vars['wpt_season'], $args ); 
		} elseif ( ! empty($wp_query->query_vars['wpt_category']) ) {
			$html = $this->get_html_for_category( $wp_query->query_vars['wpt_category'], $args ); 
		} elseif ( ! empty($wp_query->query_vars['wpt_year']) ) {
			$html = $this->get_html_for_year( $wp_query->query_vars['wpt_year'], $args ); 
		} elseif ( ! empty($wp_query->query_vars['wpt_month']) ) {
			$html = $this->get_html_for_month( $wp_query->query_vars['wpt_month'], $args ); 
		} elseif ( ! empty($wp_query->query_vars['wpt_day']) ) {
			$html = $this->get_html_for_day( $wp_query->query_vars['wpt_day'], $args ); 
		} else {
			/*
			 * The user didn't select a page.
			 * Show the full listing.
			 */
			$html = $this->get_html_grouped( $args );
		}

		/**
		 * Filter the HTML for a page in a listing.
		 *
		 * @since	0.13.4
		 * @param	string	$html_group	The HTML for this page.
		 * @param	array	$args		The arguments for the HTML of this listing.
		 */
		$html = apply_filters( 'wpt/productions/html/page', $html, $args );

		return $html;
	}

	/**
	 * Gets a list of events in HTML for a single season.
	 *
	 * @since 0.10
	 *
	 * @see WPT_Productions::get_html_grouped();
	 *
	 * @access private
	 * @param 	int $season_id	ID of the season.
	 * @param 	array $args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	private function get_html_for_season($season_id, $args = array()) {
		$args['season'] = $season_id;
		return $this->get_html_grouped( $args );
	}

	/**
	 * Gets a list of productions in HTML.
	 *
	 * The productions can be grouped inside a page by setting $groupby.
	 * If $groupby is not set then all productions are show in a single, ungrouped list.
	 *
	 * @since 	0.10
	 * @since	0.13	Added support for days, months and years.
	 *
	 * @see WPT_Production::html();
	 * @see WPT_Productions::get_html_for_season();
	 * @see WPT_Productions::get_html_for_category();
	 *
	 * @access 	protected
	 * @param 	array $args 	See WPT_Productions::get_html() for possible values.
	 * @return 	string			The HTML.
	 */
	private function get_html_grouped($args = array()) {

		$args = wp_parse_args( $args, $this->default_args_for_html );

		/*
		 * Get the `groupby` setting and remove it from $args.
		 * $args can now be passed on to any of the other `get_html_*`-methods safely
		 * without the risk of creating grouped listings within grouped listings.
		 */
		$groupby = $args['groupby'];
		$args['groupby'] = false;

		$html = '';
		switch ( $groupby ) {
			case 'day':
				$days = $this->get_days( $args );
				foreach ( $days as $day => $name ) {
					if ( $day_html = $this->get_html_for_day( $day, $args ) ) {
						$html .= '<h3 class="wpt_listing_group day">';
						$html .= apply_filters( 'wpt_listing_group_day',date_i18n( 'l d F',strtotime( $day ) ),$day );
						$html .= '</h3>';
						$html .= $day_html;
					}
				}
				break;
			case 'month':
				$months = $this->get_months( $args );
				foreach ( $months as $month => $name ) {
					if ( $month_html = $this->get_html_for_month( $month, $args ) ) {
						$html .= '<h3 class="wpt_listing_group month">';
						$html .= apply_filters( 'wpt_listing_group_month',date_i18n( 'F',strtotime( $month ) ),$month );
						$html .= '</h3>';
						$html .= $month_html;
					}
				}
				break;
			case 'year':
				$years = $this->get_years( $args );
				foreach ( $years as $year => $name ) {
					if ( $year_html = $this->get_html_for_year( $year, $args ) ) {
						$html .= '<h3 class="wpt_listing_group year">';
						$html .= apply_filters( 'wpt_listing_group_year',date_i18n( 'Y',strtotime( $year.'-01-01' ) ),$year );
						$html .= '</h3>';
						$html .= $year_html;
					}
				}
				break;
			case 'season':
				$seasons = $this->get_seasons( $args );
				foreach ( $seasons as $season_id => $season_title ) {
					if ( $season_html = $this->get_html_for_season( $season_id, $args ) ) {
						$html .= '<h3 class="wpt_listing_group season">';
						$html .= apply_filters( 'wpt_listing_group_season',$season_title,$season_id );
						$html .= '</h3>';
						$html .= $season_html;
					}
				}
				break;
			case 'category':
				$categories = $this->get_categories( $args );
				foreach ( $categories as $cat_id => $name ) {
					if ( $cat_html = $this->get_html_for_category( $cat_id, $args ) ) {
						$html .= '<h3 class="wpt_listing_group category">';
						$html .= apply_filters( 'wpt_listing_group_category',$name,$cat_id );
						$html .= '</h3>';
						$html .= $cat_html;
					}
				}
				break;
			default:
				/*
				 * No stickies in paginated or grouped views
				 */
				if (
					! empty($args['paginateby']) ||
					! empty($args['groupby'])
				) {
					$args['ignore_sticky_posts'] = true;
				}

				$productions = $this->get( $args );
				$productions = $this->preload_productions_with_events( $productions );
				$html_group = '';
				foreach ( $productions as $production ) {
					$production_args = array();
					if ( ! empty($args['template']) ) {
						$production_args = array( 'template' => $args['template'] );
					}
					$html_group .= $production->html( $production_args );
				}

				/**
				 * Filter the HTML for a group in a listing.
				 *
				 * @since	0.12.7
				 * @param	string	$html_group	The HTML for this group.
				 * @param	array	$args		The arguments for the HTML of this listing.
				 */
				$html_group = apply_filters( 'wpt/productions/html/grouped/group', $html_group, $args );

				$html .= $html_group;

		}
		return $html;
	}

	/**
	 * Gets a fully formatted listing of productions in HTML.
	 *
	 * The list of productions is compiled using filter-arguments that are part of $args.
	 * See WPT_Productions::get() for possible values.
	 *
	 * The productions can be shown on a single page or be cut up into multiple pages by setting
	 * $paginateby. If $paginateby is set then a page navigation is added to the top of
	 * the listing.
	 *
	 * The productions can be grouped inside the pages by setting $groupby.
	 *
	 * @since 0.5
	 * @since 0.10	Moved parts of this method to seperate reusable methods.
	 *				Renamed method from `html()` to `get_html()`.
	 *				Rewrote documentation.
	 *
	 * @see WPT_Listing::get_html()
	 * @see WPT_Productions::get_html_pagination()
	 * @see WPT_Productions::get_html_for_page()
	 *
	 * @param array $args {
	 * 		An array of arguments. Optional.
	 *
	 *		These can be any of the arguments used in the $filters of WPT_Productions::get(), plus:
	 *
	 *		@type array		$paginateby	Fields to paginate the listing by.
	 *									@see WPT_Productions::get_html_pagination() for possible values.
	 *									Default <[]>.
	 *		@type string    $groupby    Field to group the listing by.
	 *									@see WPT_Productions::get_html_grouped() for possible values.
	 *									Default <false>.
	 * 		@type string	$template	Template to use for the individual productions.
	 *									Default <NULL>.
	 * }
		 * @return string HTML.
	 */
	public function get_html($args = array()) {

		$html = parent::get_html( $args );

		/**
		 * Filter the formatted listing of productions in HTML.
		 *
		 * @since 0.10
		 *
		 * @param 	string $html 	The HTML.
		 * @param 	array $args 	The $args that are being used for the listing.
		 */
		$html = apply_filters( 'wpt_productions_html', $html, $args );

		return  $html;
	}

	/**
	 * Gets a list of productions in HTML for a single category.
	 *
	 * @since 0.10
		 * @since 0.10.2	Category now uses slug instead of term_id.
	 *
	 * @see WPT_Productions::get_html_grouped();
	 *
	 * @access private
	 * @param 	string $category_slug	Slug of the category.
	 * @param 	array $args 			See WPT_Productions::get_html() for possible values.
	 * @return 	string					The HTML.
	 */
	private function get_html_for_category($category_slug, $args = array()) {
		if ( $category = get_category_by_slug( $category_slug ) ) {
				$args['cat'] = $category->term_id;
		}

		return $this->get_html_grouped( $args );
	}

	/**
	 * Gets the pagination filters for a production listing.
	 * 
	 * @since	0.13.4
	 * @return 	array	The pagination filters for a production listing.
	 */
	public function get_pagination_filters() {
		
		$filters = parent::get_pagination_filters();

		$filters['day'] =  array(
			'title' => __('Days', 'wp_theatre'),
			'query_arg' => 'wpt_day',
			'callback' => array($this, 'get_days'),
		);
		
		$filters['month'] =  array(
			'title' => __('Months', 'wp_theatre'),
			'query_arg' => 'wpt_month',
			'callback' => array($this, 'get_months'),
		);
		
		$filters['year'] = array(
			'title' => __('Years', 'wp_theatre'),
			'query_arg' => 'wpt_year',
			'callback' => array($this, 'get_years'),
		);
		
		$filters['category'] = array(
			'title' => __('Categories', 'wp_theatre'),
			'query_arg' => 'wpt_category',
			'callback' => array($this, 'get_categories'),
		);
		
		$filters['season'] = array(
			'title' => __('Seasons', 'wp_theatre'),
			'query_arg' => 'wpt_season',
			'callback' => array($this, 'get_seasons'),
		);
		
		/**
		 * Filter the pagination filters for a production listing.
		 * 
		 * @since 	0.13.4
		 * @param	array	$filters	The pagination filters for a production listing.
		 */
		$filters = apply_filters('wpt/productions/pagination/filters', $filters);
		
		return $filters;
	}

	/**
	 * Gets the page navigation for an event listing in HTML.
	 *
	 * @see WPT_Listing::filter_pagination()
	 * @see WPT_Events::get_days()
	 * @see WPT_Events::get_months()
	 * @see WPT_Events::get_categories()
	 *
	 * @since 	0.10
	 * @since	0.13	Added support for days, months and years.
	 * @since	0.13.4	Show the pagination filters in the same order as the
	 *					the 'paginateby' argument.
	 *
	 * @access protected
	 * @param 	array $args     The arguments being used for the event listing.
	 *							See WPT_Events::get_html() for possible values.
	 * @return 	string			The HTML for the page navigation.
	 */
	protected function get_html_page_navigation($args = array()) {
		global $wp_query;

		$html = '';

		$paginateby = empty($args['paginateby']) ? array() : $args['paginateby'];
		
		$filters = $this->get_pagination_filters();
		
		foreach ($filters as $filter_name => $filter_options) {
			if (!empty($wp_query->query_vars[ $filter_options['query_arg'] ])) {
				$paginateby[] = $filter_name;
			}
		}
		
		$paginateby = array_unique($paginateby);
		
		foreach($paginateby as $paginateby_filter) {
			if (!empty($filters[ $paginateby_filter ])) {				
				$options = call_user_func_array( 
					$filters[ $paginateby_filter ]['callback'], 
					array( $args ) 
				);
				$html.= $this->filter_pagination(
					$paginateby_filter,
					$options,
					$args
				);			
			}
		}

		/**
		 * Filter the HTML of the page navigation for productions list. 
		 * 
		 * @since	0.13.3
		 * @param 	string 	$html	The HTML of the page navigation for an event listing.
		 * @param 	array 	$args	The arguments being used for the event listing.
		 */
		$html = apply_filters('wpt/productions/html/page/navigation', $html, $args);

		return $html;
	}

	/**
	 * Gets all productions between 'start' and 'end'.
	 *
	 * @access 	private
	 * @since	0.13
	 * @param 	string 	$start	The start time. Can be anything that strtotime understands.
	 * @param 	string 	$end	The end time. Can be anything that strtotime understands.
	 * @return 	array			The productions.
	 */
	private function get_productions_by_date($start = false, $end = false) {
		global $wp_theatre;
		$productions = array();
		if ( $start || $end ) {
			$events_args = array(
				'start' => $start,
				'end' => $end,
			);
			$events = $wp_theatre->events->get( $events_args );

			foreach ( $events as $event ) {
				$productions[] = $event->production()->ID;
			}

			$productions = array_unique( $productions );
		}
		return $productions;
	}

	/**
	 * Gets an array of all categories with productions.
	 *
	 * @since Unknown
	 * @since 0.10	Renamed method from `seasons()` to `get_seasons()`.
	 *
	 * @param 	array $filters	See WPT_Productions::get() for possible values.
	 * @return 	array 			An array of WPT_Season objects.
	 */
	public function get_seasons() {
		$productions = $this->get();
		$seasons = array();
		foreach ( $productions as $production ) {
			if ( $production->season() ) {
				$seasons[ $production->season()->ID ] = $production->season()->title();
			}
		}
		arsort( $seasons );
		return $seasons;
	}

	/**
	 * Gets a list of productions.
	 *
	 * @since 	0.5
	 * @since 	0.10	Renamed method from `load()` to `get()`.
	 * 					Added 'order' to $args.
	 * @since	0.13	Support for 'start' and 'end'.
	 * @since	0.14	Replaced get_posts() with WP_Query.
	 *
	 * @param 	array	$args {
	 *						string	$end
	 *						int 	$season
	 *						string	$start
	 * 					}
	 *					plus all $args of WP_Query.
	 * @return array 	An array of WPT_Production objects.
	 */
	public function get($args = array()) {
		
		global $wp_theatre;
		
		$args_original = $args;

		$defaults = array(
			'post_type' => WPT_Production::post_type_name,
			'post_status' => 'publish',
			'meta_query' => array(),
			'order' => 'ASC',
			'end' => false,
			'limit' => false,
			'season' => false,
			'start' => false,
			'upcoming' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		if ( $args['season'] ) {
			$args['meta_query'][] = array(
				'key' => WPT_Season::post_type_name,
				'value' => $args['season'],
				'compare' => '=',
			);
			unset($args['season']);
			$args['ignore_sticky_posts'] = true;
		}

		if ( $args['limit'] ) {
			$args['posts_per_page'] = $args['limit'];
		} else {
			$args['posts_per_page'] = -1;
		}

		if (
			$args['upcoming'] &&
			! $args['start'] &&
			! $args['end']
		) {
			_deprecated_argument( 'WPT_Productions', '0.13', __( '"upcoming" is deprecated. Use "start=\'now\'" instead.' ) );
			$args['start'] = 'now';
			unset($args['upcoming']);
		}

		/*
		 * Filter productions by date.
         *
         * Uses @see WPT_Productions::get_productions_by_date() to get a list of
		 * production IDs that match the dates. The IDs are then added a a 'post__in'
		 * argument.
		 *
         * If the 'post__in' argument is already set, then the existing list of
         * production IDs is limited to IDs that are also part of the production IDs from
         * the date selection.
         *
		 * If this results in an empty list of production IDs then further execution is
		 * halted and an empty array is returned, because there are no matching productions.
		 */
		if ( $args['start'] || $args['end'] ) {
			$productions_by_date = $this->get_productions_by_date( $args['start'], $args['end'] );
			if ( empty($args['post__in']) ) {
				$args['post__in'] = $productions_by_date;
			} else {
				$args['post__in'] = array_intersect(
					$args['post__in'],
					$productions_by_date
				);
			}
			if (empty($args['post__in'])) {
				$args['post__in'] = array(0);
			}
			unset($args['start']);
			unset($args['end']);
		}

		/**
		 * Filter the $args before doing get_posts().
		 *
		 * @since 0.9.2
		 *
		 * @param array $args The arguments to use in get_posts to retrieve productions.
		 */
		$args = apply_filters( 'wpt_productions_load_args',$args );
		$args = apply_filters( 'wpt_productions_get_args',$args );

		/**
		 * Ignore sticky productions when:
		 *
		 * - a category filter is active
		 * - in a paginated view
		 * - a post__in filter is active
		 */
		if (
			!empty($args['cat']) ||
			!empty($args['category_name']) ||
			!empty($args['category__and']) ||
			!empty($args['category__in']) ||
			!empty($args['category__not_in']) ||
			!empty($args['paginateby']) ||
			!empty($args_original['post__in'])
		) {
			$args['ignore_sticky_posts'] = true;			
		}

		$productions = array();

		// Add action to fake is_home on WP_Query.
		add_action('parse_query', array($this, 'force_is_home') );
		
		$this->query = new WP_Query( $args );
		while ( $this->query->have_posts() ) {
			$this->query->the_post();
			$productions[] = new WPT_Production($this->query->post);
		}
		wp_reset_postdata();

		// Remove action to fake is_home on WP_Query.
		remove_action('parse_query', array($this, 'force_is_home') );

		return $productions;
	}

	/**
	 * Make WP_Query think is_home is true.
	 *
	 * WP_Query only support sticky posts (production) when is_home is true.
	 * Hooked by @see WPT_Productions::get().
	 * 
	 * @since	0.14
	 * @param 	WP_Query	$query
	 * @return 	void
	 */
	function force_is_home( $query ) {
		$query->is_home = true;
	}

	/**
	 * Preloads productions with their events.
	 *
	 * Sets the events of a each production in a list of productions with a single query.
	 * This dramatically decreases the number of queries needed to show a listing of productions.
	 *
	 * @since 	0.10.14
	 * @access 	private
	 * @param 	array	$productions	An array of WPT_Production objects.
	 * @return 	array					An array of WPT_Production objects, with the events preloaded.
	 */
	private function preload_productions_with_events($productions) {
		global $wp_theatre;
		$production_ids = array();

		foreach ( $productions as $production ) {
			$production_ids[] = $production->ID;
		}

		$production_ids = array_unique( $production_ids );

		$events = get_posts(
			array(
				'post_type' => WPT_Event::post_type_name,
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => WPT_Production::post_type_name,
						'value' => array_unique( $production_ids ),
						'compare' => 'IN',
					),
					array(
						'key' => $wp_theatre->order->meta_key,
						'value' => time(),
						'compare' => '>=',
					),
				),
				'order' => 'ASC',
			)
		);

		$productions_with_keys = array();

		foreach ( $events as $event ) {
			$production_id = get_post_meta( $event->ID, WPT_Production::post_type_name, true );
			if ( ! empty($production_id) ) {
				$productions_with_keys[ $production_id ][] = new WPT_Event( $event );
			}
		}

		for ( $i = 0; $i < count( $productions );$i++ ) {
			if ( in_array( $productions[ $i ]->ID, array_keys( $productions_with_keys ) ) ) {
				$productions[ $i ]->events = $productions_with_keys[ $productions[ $i ]->ID ];
			}
		}
		 return $productions;
	}

	/**
	 * @deprecated 0.10
	 * @see WPT_Productions::get_categories()
	 */
	public function categories($filters = array()) {
		_deprecated_function( 'WPT_Productions::categories()', '0.10', 'WPT_Productions::get_categories()' );
		return $this->get_categories( $filters );
	}

	/**
	 * @deprecated 0.10
	 * @see WPT_Productions::get_seasons()
	 */
	public function seasons($filters = array()) {
		_deprecated_function( 'WPT_Productions::get_seasons()', '0.10', 'WPT_Productions::get_seasons()' );
		return $this->get_seasons( $filters );
	}


}
?>