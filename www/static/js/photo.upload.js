/*
 * jQuery File Upload Plugin JS Example 7.0
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global $, window, document */

$(function() {
	'use strict';

	var uploadsCount = 0, processing = 0;

	function changeFilesCount(diff) {

		uploadsCount += diff;
		console.log('changeFilesCount', diff, uploadsCount, processing);

		if (uploadsCount === 0) {
			$('#noPhotosAlert').show();
			$('#photosShareButton').hide();
			$('#photosNotSharedYetAlert').hide();
		} else {
			$('#noPhotosAlert').hide();
			$('#photosNotSharedYetAlert').show();
			if (processing > 0) {
				$('#photosShareButton').hide();
			} else {
				$('#photosShareButton').show();
			}
		}
	}

	changeFilesCount(0);

	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload({
		// Uncomment the following to send cross-domain cookies:
		// xhrFields: {withCredentials: true},
		url : '/photos/upload.ajax.php',
		sequentialUploads : true,
		autoUpload : true,
		acceptFileTypes : '/(\.|\/)(jpe?g)$/i'
	});

	$('#fileupload').bind('fileuploaddestroyed', function(e, data) {
		console.log('fileuploaddestroyed', data);
		changeFilesCount(-1);
	});
	$('#fileupload').bind('fileuploadadded', function(e, data) {
		console.log('fileuploadadded', data);
		processing += data.files.length;
		$('#noPhotosAlert').hide();
		changeFilesCount(0);
	});
	$('#fileupload').bind('fileuploadstop', function(e, data) {
		console.log('fileuploadstop', data);
		processing = Math.max(processing - 1, 0);
		changeFilesCount(0);
	});
	$('#fileupload').bind('fileuploadcompleted', function(e, data) {
		console.log('fileuploadcompleted', data);
		processing = Math.max(processing - data.result.files.length, 0);
		changeFilesCount(data.result.files.length);
	});

	// // Enable iframe cross-domain access via redirect option:
	// $('#fileupload').fileupload('option', 'redirect',
	// window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s'));

	// if (window.location.hostname === 'blueimp.github.com') {
	// // Demo settings:
	// $('#fileupload').fileupload('option', {
	// url: '//jquery-file-upload.appspot.com/',
	// maxFileSize: 5000000,
	// acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	// process: [
	// {
	// action: 'load',
	// fileTypes: /^image\/(gif|jpeg|png)$/,
	// maxFileSize: 20000000 // 20MB
	// },
	// {
	// action: 'resize',
	// maxWidth: 1440,
	// maxHeight: 900
	// },
	// {
	// action: 'save'
	// }
	// ]
	// });
	// // Upload server status check for browsers with CORS support:
	// if ($.support.cors) {
	// $.ajax({
	// url: '//jquery-file-upload.appspot.com/',
	// type: 'HEAD'
	// }).fail(function () {
	// $('<span class="alert alert-error"/>')
	// .text('Upload server currently unavailable - ' +
	// new Date())
	// .appendTo('#fileupload');
	// });
	// }
	// } else {
	// Load existing files:
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		// xhrFields: {withCredentials: true},
		url : $('#fileupload').fileupload('option', 'url'),
		dataType : 'json',
		context : $('#fileupload')[0]
	}).done(function(result) {
		$(this).fileupload('option', 'done').call(this, null, {
			result : result
		});
	});
	// }

});
