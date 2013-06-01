function getOriginalWidthOfImg(img_element) {
	var t = $('<img>');
	t.attr("src", img_element.attr("src"));
	return t.width();
}

window.disableDraggingFor = function(element) {
	// this works for FireFox and WebKit in future according to
	// http://help.dottoro.com/lhqsqbtn.php
	element.draggable = false;
	// this works for older web layout engines
	element.onmousedown = function(event) {
		event.preventDefault();
		return false;
	};
}

function detectFaces(photos_id) {
	var /* area = $('#photo_' + photos_id + '_form .presences'), */photo = $('img#photo_'
			+ photos_id);

	console.log('detectFaces', photos_id);

	var coords = photo.faceDetection({
		complete : function(img, coords) {

			var ratio = img.width() / img[0].naturalWidth;

			console.log('Done!', img, img.width(), img[0].naturalWidth, ratio);

			jQuery.each(coords, function(index, coord) {
				console.log("Coord", JSON.stringify(coord));
				$('<div>', {
					'class' : 'face',
					'css' : {
						'position' : 'absolute',
						'left' : (coord.positionX * ratio) + 'px',
						'top' : (coord.positionY * ratio) + 'px',
						'width' : (coord.width * ratio) + 'px',
						'height' : (coord.height * ratio) + 'px'
					}
				}).appendTo(photo.parent());

			});
		},
		error : function(img, code, message) {
			console.log('Error', img, code, message);
		}
	});
	console.log('Coords', coords);
	// for ( var i = 0; i < coords.length; i++) {
	// area.inject($('<div>', {
	// 'class' : 'face',
	// 'css' : {
	// 'position' : 'absolute',
	// 'left' : coords[i].positionX + 'px',
	// 'top' : coords[i].positionY + 'px',
	// 'width' : coords[i].width + 'px',
	// 'height' : coords[i].height + 'px'
	// }
	// }));
	// }

}

function deletePicture(id) {
	if (confirm("Souhaitez-vous retirer cette photo du site?") === true) {
		jQuery.ajax('./photo.edit.ajax.php', {
			type : 'POST',
			data : {
				'id' : id,
				action : 'delete'
			},
			cache : false,
			error : function(jqXhr, res) {
				$('#photo-edit-' + id).animate({
					opacity : 1
				});
				console.log(res, jqXhr);
				// $('#photo-edit-' + id).hide();
			},
			success : function(data, status, jqXhr) {
				$('#photo-edit-' + id).remove();

				window.editedPhoto.splice(window.editedPhoto.indexOf(id, 1));

				checkNoPhotos();
			}
		});

		$('#photo-edit-' + id).animate({
			opacity : 0.2
		});
	}
}

function checkNoPhotos() {
	console.log('checkNoPhotos', window.editedPhoto);
	if (window.editedPhoto.length === 0) {
		window.location = '/photos/';
	}

}

function addPresence(photos_id, personnes_id, drawing) {
	console.log('addPresence', photos_id, personnes_id);

	var data = {
		id : photos_id,
		action : 'addPresence',
		personnes_id : personnes_id
	};

	if (window.photos[photos_id].presences.drawing !== null) {
		data.drawing = window.photos[photos_id].presences.drawing;
		window.photos[photos_id].presences.selection.remove();
	}

	jQuery.ajax('./photo.edit.ajax.php', {
		type : 'POST',
		data : data,
		cache : false,
		error : function(jqXhr, res) {
			console.log(res, jqXhr);
		},
		success : function(data, status, jqXhr) {

			console.log("success");
			console.log(data);
			console.log(status);

			window.photos[photos_id].presences.present.push(personnes_id);

			$('#photo-edit-' + photos_id + ' .presences').append(data);
			checkPresences(photos_id);
		}
	});

	window.photos[photos_id].presences.drawing = null;
	window.photos[photos_id].presences.searching = null;
}

function removePres(id, photos_id, personnes_id) {
	console.log('removePres', id, photos_id, personnes_id);

	jQuery.ajax('./photo.edit.ajax.php', {
		type : 'POST',
		data : {
			id : id,
			action : 'removePresence',
			personnes_id : personnes_id,
			photos_id : personnes_id,
		},
		cache : false,
		error : function(jqXhr, res) {
			console.log(res, jqXhr);
		},
		success : function(data, status, jqXhr) {

			console.log("success removing ", personnes_id);
			$('#presence-' + id).remove();

			window.photos[photos_id].presences.present.splice(
					window.photos[photos_id].presences.present
							.indexOf(personnes_id), 1);
			checkPresences(photos_id);
		}
	});

	$('#presence-' + id).fadeOut();

}

function checkPresences(id) {

	console.log('checkPresences', id, window.photos[id].presences.present);
	if (window.photos[id].presences.present.length > 0) {
		$('#photo-edit-' + id + ' .noPresenceAlert').hide();
	} else {
		$('#photo-edit-' + id + ' .noPresenceAlert').show();
	}
}

function tracePortrait(photos_id, id) {
	console.log('tracePortrait', photos_id, id);

	if (id !== undefined) {
		window.photos[photos_id].presences.searching = id;
	} else {
		id = window.photos[photos_id].presences.searching;
	}
	if (window.photos[photos_id].presences.searching !== null) {
		if (window.photos[photos_id].presences.drawing !== null) {
			var data = {
				photos_id : photos_id,
				action : 'tracePortrait',
				id : id,
				drawing : window.photos[photos_id].presences.drawing
			};

			jQuery.ajax('./photo.edit.ajax.php', {
				type : 'POST',
				data : data,
				cache : false,
				error : function(jqXhr, res) {
					console.log(res, jqXhr);
				},
				success : function(data, status, jqXhr) {

					console.log("success");
					console.log(data);
					console.log(status);

					$('#presence-' + id).replaceWith(data).fadeIn();

					checkPresences(photos_id);
				}
			});
			window.photos[photos_id].presences.drawing = null;
			window.photos[photos_id].presences.searching = null;
			window.photos[photos_id].presences.selection.remove();
			$('#presence-' + id).fadeOut();
			$('#photo-' + photos_id + ' img').css({
				'background-color' : ''
			});

		} else {
			$('#photo-' + photos_id + ' img').css({
				'background-color' : 'orange'
			});
		}
	}
}

function watchRect(container, photos_id) {
	window.photos[photos_id].presences.selection = $('<div>').addClass('face');
	console.log('watching ', container);
	container
			.on(
					'mousedown',
					function(e) {
						console.log('mousedown', e.currentTarget, e);
						var offset = $(e.currentTarget).offset();
						var click_y = e.pageY - offset.top, click_x = e.pageX
								- offset.left, width = false, height = false, new_x = false, new_y = false;

						window.photos[photos_id].presences.selection.css({
							'top' : click_y,
							'left' : click_x,
							'width' : 0,
							'height' : 0
						});
						window.photos[photos_id].presences.selection
								.appendTo(container);

						container
								.on(
										'mousemove',
										function(e) {
											var offset = $(e.currentTarget)
													.offset();
											var move_x = e.pageX - offset.left, move_y = e.pageY
													- offset.top;
											width = Math.abs(move_x - click_x);
											height = Math.abs(move_y - click_y);

											new_x = (move_x < click_x) ? (click_x - width)
													: click_x;
											new_y = (move_y < click_y) ? (click_y - height)
													: click_y;

											window.photos[photos_id].presences.selection
													.css({
														'width' : width,
														'height' : height,
														'top' : new_y,
														'left' : new_x
													});

										});
						container.on('mouseup', function(e) {
							var image = container.find('img');
							if (width >= 5 && height >= 5) {
								image.trigger('faceTraced', {
									x : new_x
											- parseFloat(image
													.css('padding-left')),
									y : new_y
											- parseFloat(image
													.css('padding-top')),
									w : width,
									h : height,
									totalW : image.width(),
									totalH : image.height()
								});
							}
							container.off('mousemove mouseup');
						});
					});

}
