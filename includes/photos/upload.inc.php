<?php
// <!-- Bootstrap Image Gallery styles -->
$context->addCssFile('/static/libs/Bootstrap-Image-Gallery/css/bootstrap-image-gallery.min.css');

// <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
$context->addCssFile('/static/libs/jQuery-File-Upload/css/jquery.fileupload-ui.css');

$context
		->addHtmlInline(
				'<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="/static/libs/jQuery-File-Upload/css/jquery.fileupload-ui-noscript.css"></noscript>
<!-- Shim to make HTML5 elements usable in older Internet Explorer versions -->
<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->');

$context->addCssFile('/static/css/photo.upload.css');

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<!-- The file upload form used as target for the file upload widget -->
<form id="fileupload" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data" class="block clearfix">
	<!-- Redirect browsers with JavaScript disabled to the origin page -->
	<noscript>
		<input type="hidden" name="redirect" value="http://blueimp.github.com/jQuery-File-Upload/">
	</noscript>
	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<!--         <div class="row fileupload-buttonbar"> -->
	<h3 class="clearfix">Partager de nouvelles photos</h3>
	<!-- The global progress information -->
	<div class="fileupload-progress fade">
		<!-- The global progress bar -->
		<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
			<div class="bar" style="width: 0%;"></div>
		</div>
		<!-- The extended global progress information -->
		<div class="progress-extended"></div>
	</div>
	<!--         </div> -->
	<!-- The loading indicator is shown during file processing -->
	<div class="fileupload-loading"></div>
	<!-- The table listing the files available for upload/download -->
	<table role="presentation" class="table table-striped">
		<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
	</table>
	<div class="alert" id="noPhotosAlert">
		<h4>Aucune photo sélectionnée</h4>
		Merci de sélectionner les photos à partager en cliquant sur le bouton ci-dessous.
	</div>
	<div class="alert alert-info" id="photosNotSharedYetAlert">
		<h4>Partage en préparation...</h4>
		Les photos sélectionnées ne sont visibles par personne tant que vous n'avez pas cliqué sur le bouton "Partager" ci-dessous.
	</div>
	<div class="form-actions">
		<a class="btn btn-inverse" href="?action=cancel">Annuler</a>
		<span class="btn btn-success fileinput-button">
			<i class="icon-plus icon-white"></i>
			<span>Sélectionner d'avantage de photos...</span>
			<input type="file" name="files[]" multiple>
		</span>
		<a href="?action=share" class="btn btn-primary" id="photosShareButton">Partager ces photos</a>
	</div>
</form>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td>{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary start">
                    <i class="icon-upload icon-white"></i>
                    <span>Start</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td>{% if (!i) { %}
            <button class="btn btn-warning cancel">
                <i class="icon-ban-circle icon-white"></i>
                <span>Annuler</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" data-gallery="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" data-gallery="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td>
            <button class="btn btn-danger delete" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                <i class="icon-trash icon-white"></i>
                <span>Effacer</span>
            </button>
        </td>
    </tr>
{% } %}
</script>
<?php
//<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
$context->addJsFile("/static/libs/jQuery-File-Upload/js/vendor/jquery.ui.widget.js");
//<!-- The Templates plugin is included to render the upload/download listings -->
$context->addJsFile("/static/libs/JavaScript-Templates/js/tmpl.min.js");
//<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
$context->addJsFile("/static/libs/JavaScript-Load-Image/js/load-image.min.js");
//<!-- The Canvas to Blob plugin is included for image resizing functionality -->
$context->addJsFile("/static/libs/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js");

//<!-- Bootstrap JS and Bootstrap Image Gallery are not required, but included for the demo -->
// $context->addJsFile("http://blueimp.github.com/cdn/js/bootstrap.min.js");
$context->addJsFile("/static/libs/Bootstrap-Image-Gallery/js/bootstrap-image-gallery.min.js");

//<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
$context->addJsFile("/static/libs/jQuery-File-Upload/js/jquery.iframe-transport.js");
//<!-- The basic File Upload plugin -->
$context->addJsFile("/static/libs/jQuery-File-Upload/js/jquery.fileupload.js");
//<!-- The File Upload file processing plugin -->
$context->addJsFile("/static/libs/jQuery-File-Upload/js/jquery.fileupload-process.js");
//<!-- The File Upload user interface plugin -->
$context->addJsFile("/static/libs/jQuery-File-Upload/js/jquery.fileupload-ui.js");
//<!-- The main application script -->
$context->addJsFile("/static/js/photo.upload.js");

$context
		->addHtmlInline(
				'<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="/static/libs/jQuery-File-Upload/js/cors/jquery.xdr-transport.js"></script><![endif]-->');
