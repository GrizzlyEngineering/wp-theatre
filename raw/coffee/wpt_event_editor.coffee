class wpt_event_editor
	constructor: ->
		@init_datetime_inputs()
		@init_edit_links()
		@init_delete_links()
		@init_create()
		
	init_datetime_inputs : ->
	
		@event_date = jQuery '#wpt_event_editor_event_date'
		@enddate = jQuery '#wpt_event_editor_enddate'
	
		@event_date.wpt_datetimepicker
			defaultDate: wpt_event_editor_defaults.event_date
			format : wpt_event_editor_defaults.datetime_format
			step: 15
			onChangeDateTime: (event_date, input) =>
				if event_date?
					enddate = new Date @enddate.val()	
					if not @enddate.val() or (enddate < event_date)
						enddate = new Date event_date.getTime() + wpt_event_editor_defaults.duration * 1000					
						@enddate.val enddate.dateFormat wpt_event_editor_defaults.datetime_format
			
		@enddate.wpt_datetimepicker	
			format : wpt_event_editor_defaults.datetime_format
			step: 15
		
	init_edit_links : ->
		jQuery('.wpt_event_editor_listing_action_edit').unbind('click').click (e) =>
		
			@close_all()
			
			event_row = jQuery(e.currentTarget).parents 'tr'
			event_id = event_row.data 'event_id'
			
			edit_form_id = 'wpt_event_editor_edit_form_'+event_id
			
			event_row.after '<tr data-event_id="'+event_id+'" class="wpt_event_editor_edit_form"><td colspan="'+event_row.children().length+'" id="'+edit_form_id+'"><div class="spinner"></div></td></tr>'
			event_row.addClass 'hidden'
				
			edit_form_container = jQuery '#'+edit_form_id
			
			data =
				'action': 'wpt_event_editor_get_edit_form'
				'production_id' : jQuery('#post_ID').val()
				'event_id' : event_id
				'nonce': wpt_event_editor_security.nonce
	
			edit_form_container.load ajaxurl, data, =>
				@init_datetime_inputs()
				@init_edit edit_form_id
				
			
			false

	init_delete_links : ->
		jQuery('.wpt_event_editor_listing_action_delete').unbind('click').click (e) =>
			if confirm wpt_event_editor_defaults.confirm_delete_message
				data =
					'action': 'wpt_event_editor_delete_event'
					'event_id': jQuery(e.currentTarget).parents('tr').data 'event_id'
					'nonce': wpt_event_editor_security.nonce
				jQuery('.wpt_event_editor_listing').load ajaxurl, data, =>
					@init_delete_links()
			false

	init_create : ->
	
		@create = jQuery '.wpt_event_editor_create'
		open = @create.find '.wpt_event_editor_create_open'
		open.click =>
			@close_all()
			@create.addClass 'open'
			false

		cancel = @create.find '.wpt_event_editor_form_cancel'
		cancel.click =>
			@create.removeClass 'open'
			false
		
		save = @create.find '.wpt_event_editor_form_save'
		save.click =>
		
			form = jQuery '#post'
		
			data =
				'action': 'wpt_event_editor_save_event'
				'post_data' : form.serialize()
				'nonce': wpt_event_editor_security.nonce
			jQuery('.wpt_event_editor_listing').load ajaxurl, data, =>
				@init_delete_links()
				@init_edit_links()
				@create.removeClass 'open'
				@reset_create_form()
			false
	
	init_edit : (edit_form_id) ->
		
		cancel = jQuery('#'+edit_form_id).find '.wpt_event_editor_form_cancel'
		cancel.click =>
			@close_all()
			false

		save = jQuery('#'+edit_form_id).find '.wpt_event_editor_form_save'
		save.click (e) =>
		
			form = jQuery '#post'
		
			data =
				'action': 'wpt_event_editor_save_event'
				'post_data' : form.serialize()
				'event_id' : jQuery(e.currentTarget).parents('tr').data 'event_id'
				'nonce': wpt_event_editor_security.nonce
			jQuery('.wpt_event_editor_listing').load ajaxurl, data, =>
				@init_delete_links()
				@init_edit_links()
			false
		
	
	reset_create_form : ->
		
		container = @create.find '.wpt_event_editor_create'

		data =
			'action': 'wpt_event_editor_reset_create_form'
			'production_id' : jQuery('#post_ID').val()
			'nonce': wpt_event_editor_security.nonce

		container.load ajaxurl, data, =>
			@init_datetime_inputs()
		
	close_all : ->
		
		jQuery('.wpt_event_editor_edit_form').detach()
		jQuery('.wpt_event_editor_listing tr.hidden').removeClass 'hidden'
		jQuery('.wpt_event_editor_create').removeClass 'open'

jQuery ->
	new wpt_event_editor
