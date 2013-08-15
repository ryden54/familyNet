function removeRelativePerson(link, id, token, linked_personnes_id) {
	var url = '/famille/membre.edit.ajax.php';
	jQuery.post(url, {
		id : id,
		linked_personnes_id : linked_personnes_id,
		link : link,
		token : token
	});
}

function removeAddr(IdPersonneCoordonnees, id, token) {
	var url = '/famille/membre.edit.ajax.php';
	jQuery.post(url, {
		id : id,
		IdPersonneCoordonneesDelete : IdPersonneCoordonnees,
		token : token
	}, function(data, textStatus, jqXHR) {
		console.log('Success');
	});
}

var newAdressModal = null;

function addAdress(token, personnes_id, coordonnees_id) {
	if (coordonnees_id !== undefined) {
		newAdressModal
				.find('.modal-body')
				.html(
						'<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>');
		window.location = "/famille/membre.edit.php?id=" + personnes_id
				+ "&new_coordonnees_id=" + coordonnees_id + "&token=" + token;
	} else {

		if ($('#newCoordsZip')[0].value.length >= 4) {
			var url = '/famille/membre.edit.adress.ajax.php?data='
					+ JSON.stringify({
						zip : $('#newCoordsZip')[0].value,
						city: $('#newCoordsCity')[0].value,
						country: $('#newCoordsCountry')[0].value,
						tel: $('#newCoordsTel')[0].value,
						fax: $('#newCoordsFax')[0].value,
						addr : $('#newCoordsAddr')[0].value
					}) + '&personnes_id=' + personnes_id + '&token=' + token;

			if (newAdressModal === null) {
				newAdressModal = $('#newAdressModal').modal({
					show : false
				});
			}
			newAdressModal
					.find('.modal-body')
					.html(
							'<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>');
			newAdressModal.modal('show');
			newAdressModal.find('.modal-body').load(url);
		} else {
			$('#newCoordsZip')[0].focus();
		}
	}
}

var pickPersonModal = null;
function setRelativePerson(link, personnes_id, token, linked_personnes_id) {
	console.log('setRelativePerson');
	var url = '/famille/membre.edit.person.ajax.php', form = $('#pickPersonModal #pickPersonForm');
	if (pickPersonModal === null) {
		pickPersonModal = $('#pickPersonModal').modal({
			show : false
		});
	}

	pickPersonModal
			.find('.modal-body')
			.html(
					'<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>');

	link = (link === null ? form.find(':input[name=link]').val() : link);
	personnes_id = (personnes_id === null ? form.find(':input[name=id]').val()
			: personnes_id);
	token = (token === null ? form.find(':input[name=token]').val() : token);

	console.log(linked_personnes_id);

	if (linked_personnes_id === undefined) {
		console.log("setting link", link, form.find(':input[name=link]'));
		form.find(':input[name=link]').val(link);
		if (link !== 'IdParent1' && link !== 'IdParent2') {
			$('#pickPersonModal .modal-footer .btn.none').hide();
		} else {
			$('#pickPersonModal .modal-footer .btn.none').show();
		}

		pickPersonModal.modal('show');
		$('#pickPersonModal').find('.modal-body').load(url, {
			link : link,
			personnes_id : personnes_id,
			token : token
		});
	} else if (linked_personnes_id === 0) {
		form.get(0).setAttribute('action', '/famille/membre.edit.php');
		form.find(':input[name=id]').val(0);
		form.find(':input[name=linked_personnes_id]').val(personnes_id);
		form.submit();
	} else {
		jQuery
				.ajax(
						url,
						{
							data : {
								personnes_id : personnes_id,
								token : token,
								link : link,
								linked_personnes_id : (linked_personnes_id === null ? -1
										: linked_personnes_id)
							},
							cache : false,
							complete : function() {
								window.location = '/famille/membre.edit.php?id='
										+ personnes_id;
							}
						});
	}

}

$('#newCoordsZip').ready(function() {
	$(window).keydown(function(event) {
		if ($('#newCoordsZip').is(':focus') === true) {
			if (event.keyCode == 13) {
				event.preventDefault();
				return false;
			}
		}
	});
});