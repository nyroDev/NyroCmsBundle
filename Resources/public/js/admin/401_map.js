jQuery(function($) {
	var geoposition = $('[data-geposition]');
	
	if (geoposition.length) {
		var defaultZoom = 10,
			defaultCenter = [47.137222, 6.436111],
			gmapUrl = 'https://maps.googleapis.com/maps/api/js?v=3.20&sensor=false',
			gmapLoaded = false,
			gmapLoading = false,
			waitingClbs = [],
			styles = [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
			loadGmap = function(clb) {
				if (!gmapLoaded) {
					waitingClbs.push(clb);
					if (!gmapLoading) {
						gmapLoading = true;
						window.gMapClb = function() {
							window.gMapClb = null;
							gmapLoaded = true;
							gmapLoading = false;
							$.each(waitingClbs, function(i, v) {
								v();
							});
						};
						$.ajax({url: gmapUrl+'&callback=gMapClb', dataType: 'script'});
					}
				} else {
					clb();
				}
			};
		
		loadGmap(function() {
			geoposition.each(function() {
				var me = $(this).hide(),
					mapCont = $('<div class="mapGeoloc"></div>').appendTo(me.closest('.form_row')),
					search = $('<input type="search" placeholder="Rechercher un lieu"/>').insertAfter(me),
					GCenter = new google.maps.LatLng(defaultCenter[0], defaultCenter[1]),
					GMap = new google.maps.Map(mapCont.get(0), {
						center: GCenter,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						mapTypeControl: false,
						zoom: defaultZoom,
						streetViewControl: true,
						styles: styles
					}),
					GMarker = new google.maps.Marker({
						map: GMap,
						visible: true,
						draggable: true,
						position: GCenter,
						icon: me.data('geposition')
					}),
					geocoder = new google.maps.Geocoder(),
					updateField = function() {
						me.val(GMarker.getPosition().lat()+','+GMarker.getPosition().lng());
					};
					
					search
						.on('change', function() {
							geocoder.geocode({address: search.val()}, function(results, status) {
								if (results.length && status == google.maps.GeocoderStatus.OK) {
									GMap.fitBounds(results[0].geometry.viewport);
									GMarker.setPosition(results[0].geometry.location);
									updateField();
								}
							});
						})
						.on('keypress', function(e) {
							if (e.which == 13) {
								e.preventDefault();
								search.trigger('change');
							}
						});
					
					if (me.val().length > 0) {
						var tmp = me.val().split(','),
							position = new google.maps.LatLng(tmp[0], tmp[1]);
						GMarker.setPosition(position);
						GMap.setCenter(position);
					}
					google.maps.event.addListener(GMarker, 'dragend', function() {
						updateField();
					});
			});
		});
	}
});