<div id="catg-images">
	<div id="catg-images-droppable"></div>

	<div id="catg-images-gallery"></div>
	<br class="cb" />
</div>

<script type="text/javascript">
	[{include file=$upload_script_path}]

	(function () {
		'use strict';

		var $container = $('catg-images'),
			$droppable = $('catg-images-droppable'),
			$gallery = $('catg-images-gallery'),
			images = '[{$images}]'.evalJSON(),
			thumb_size = 300,
			$zoom_out_button = new Element('button', {type:'button', className:'btn fr', id:'catg-images-zoom-out'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/magnifier_zoom_out.png'})),
			$zoom_null_button = new Element('button', {type:'button', className:'btn fr', id:'catg-images-zoom-null'}).update(new Element('img', {src:'[{$dir_images}]icons/target.png'})),
			$zoom_in_button = new Element('button', {type:'button', className:'btn fr', id:'catg-images-zoom-in'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/magnifier_zoom_in.png'})),
			i;

		[{if $is_allowed_to_edit}]
		new qq.FileUploader({
			element: $droppable,
			action: '[{$ajax_url}]&action=save',
			multiple: true,
			autoUpload: true,
			sizeLimit: 5242880, // About 5 MB.
			allowedExtensions: ['bmp', 'png', 'jpg', 'jpeg', 'gif'],
			onUpload:function(id){
				// Create a blank "thumbnail" for the GUI.
				$gallery.insert(render_thumb(id));
			},
			onComplete: function (id, filename, response) {
				if (response.success && response.data.success && response.data.data > 0) {
					load_image(id, response.data.data);
				}
			},
			onProgress: function (id, filename, loaded, total) {
				var $bar = $('thumb-' + id).down('.bar');

				new Effect.Morph($bar, {style:'width:' + ((loaded / total) * 100) + '%', duration: 0.1});
			},
			onError: function (id, filename, response) {
				// This does not get triggered reliably...
				idoit.Notify.error(response.message || '[{isys type="lang" ident="LC__CATG__IMAGES__UPLOAD_IMAGE_ERROR"}] "' + filename + '".', {sticky:true})
			},
			dragText: '[{isys type="lang" ident="LC_FILEBROWSER__DROP_FILE"}]',
			multipleFileDropNotAllowedMessage: '[{isys type="lang" ident="LC_FILEBROWSER__SINGLE_FILE_UPLOAD"}]',
			uploadButtonText: '<img src="[{$dir_images}]icons/silk/zoom.png" alt="" class="vam mr5" style="margin-top:-1px; height:16px;" /><span style="vertical-align:baseline;">[{isys type="lang" ident="LC__UNIVERSAL__FILE_ADD"}]</span>',
			cancelButtonText: '&nbsp;',
			failUploadText: '[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]'
		});
		[{/if}]

		for (i in images) {
			if (images.hasOwnProperty(i)) {
				$gallery.insert(render_thumb('a' + i));

				load_image('a' + i, images[i]);
			}
		}

		$container.on('click', 'button.image-deleter', function (ev) {
			var $button = ev.findElement('button').disable(),
				$gallery_item = $button.up('.gallery-item'),
				$gallery_thumb = $gallery_item.down('img.thumb');

			if (confirm('[{isys type="lang" ident="LC__CATG__IMAGES__DELETE_IMAGE_CONFIRM" p_bHtmlEncode=false}]')) {
				new Ajax.Request('[{$ajax_url}]&action=delete', {
					parameters: {
						image_id: $gallery_item.readAttribute('data-image-id')
					},
					method: "post",
					onSuccess: function (transport) {
						var json = transport.responseJSON,
							destination_dimension = Math.ceil($gallery_item.getWidth() * 0.8),
							destination_margin = Math.floor(($gallery_item.getWidth() - destination_dimension) / 2) + 5;

						if (json.success && json.data) {
							new Effect.Morph($gallery_item, {
								style: 'width:' + destination_dimension + 'px; height:' + destination_dimension + 'px; margin:' + destination_margin + 'px; margin-bottom:0; opacity:0;',
								afterFinish: function () {
									$gallery_item.remove();
								}
							});

							// Morph the thumb-margin simultaneously for a nicer effect.
							$gallery_thumb.morph('margin:' + ($gallery_thumb.getStyle('margin-top') * 0.5) + 'px ' + ($gallery_thumb.getStyle('margin-left') * 0.5) + 'px;');
						} else {
							$button.enable();
							idoit.Notify.error(json.message || '[{isys type="lang" ident="LC__CATG__IMAGES__DELETE_IMAGE_ERROR"}]', {sticky:true});
						}
					}
				});
			}
		});

		$container.on('click', 'img.thumb', function (ev) {
			var $popup = $('popup'),
				$img = ev.findElement('img'),
				window_size = document.viewport.getDimensions(),
				width = $img.naturalWidth + 30,
				height = $img.naturalHeight + 30,
				ratio;

			if (width >= window_size.width || height >= window_size.height) {
				if ((window_size.width - width) > (window_size.height - height)) {
					ratio = (height / window_size.height);
				} else {
					ratio = (width / window_size.width);
				}

				width = (width / ratio) - 30;
				height = (height / ratio) - 30;
			}

			width -= 20;
			height -= 20;

			$popup
				.update(new Element('div', {className:'p5', style:'background:url("[{$dir_images}]pattern3.png"); overflow: auto;'})
					.update(new Element('img', {src:$img.readAttribute('src'), className:'m10 mouse-pointer', style:'width:' + (width - 30) + 'px; height:' + (height - 30) + 'px;'})));

			$popup.down('img').on('click', function () {
				popup_close();
			});

			popup_open('popup', width, height);
		});

		$zoom_out_button.on('click', function () {
			thumb_size -= 50;

			repaint();
		});

		$zoom_null_button.on('click', function () {
			thumb_size = 300;

			repaint();
		});

		$zoom_in_button.on('click', function () {
			thumb_size += 50;

			repaint();
		});

		function render_thumb (id) {
			var $thumb = new Element('div', {id: 'thumb-' + id, className:'gallery-item', 'data-image-id': 0, style:'width:' + thumb_size + 'px; height:' + thumb_size + 'px'}),
				$deleter = new Element('button', {type:'button', className:'btn image-deleter'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png', className:'mr5'})).insert(new Element('span').update('[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]')),
				$img = new Element('img', {className:'thumb'}).hide(),
				$loader = new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'loader'}),
				$progress = new Element('div', {class:'progress-bar'}).update(new Element('div', {className:'bar'}));

			[{if !$is_allowed_to_delete}]
			$deleter = '';
			[{/if}]

			return $thumb.update($deleter).insert($img).insert($loader).insert($progress);
		}

		function load_image (id, data) {
			var $thumb = $('thumb-' + id).writeAttribute('data-image-id', data),
				$img = $thumb.down('.thumb');

			$img.on('load', function (ev) {
				var $img = ev.findElement('img'),
					viewer_title = '[{isys type="lang" ident="LC__CATG__IMAGES__VIEW_BUTTON" p_bHtmlEncode=false}]'.replace('%s', $img.naturalWidth + 'x' + $img.naturalHeight);

				$img
					.writeAttribute('title', viewer_title)
					.setStyle({margin: ((thumb_size - $img.getHeight()) / 2) + 'px ' + ((thumb_size - $img.getWidth()) / 2) + 'px'})
					.next('.loader').fade()
					.next('.progress-bar').morph('height:0; opacity:0;');

				// The "appear" effect needs a callback, because some browsers will write attributes like ' width="299" '.
				new Effect.Appear($img, {
					duration: 0.5,
					afterFinish: function () {
						$img.writeAttribute({width:null, height:null});
					}
				});

				// Update the "drop area" to the new height.
				if ($container.down('.qq-upload-drop-area')) {
					$container.down('.qq-upload-drop-area').setStyle({height:$container.getHeight() + 'px'});
				}
			});

			$img.writeAttribute('src', '[{$image_url}]&[{$smarty.const.C__GET__FILE__ID}]=' + data);
		}

		function repaint () {
			$container.select('.gallery-item').each(function ($thumb) {
				var $img = $thumb
					.setStyle({width: thumb_size + 'px', height: thumb_size + 'px'})
					.down('.thumb');

				$img.setStyle({margin: ((thumb_size - $img.getHeight()) / 2) + 'px ' + ((thumb_size - $img.getWidth()) / 2) + 'px'});
			});

			// Update the "drop area" to the new height.
			if ($container.down('.qq-upload-drop-area')) {
				$container.down('.qq-upload-drop-area').setStyle({height:$container.getHeight() + 'px'});
			}
		}

		// Add the "zoom" buttons.
		$droppable
			.insert($zoom_out_button)
			.insert($zoom_null_button)
			.insert($zoom_in_button);
	})();
</script>

<style type="text/css">
	#catg-images-droppable {
		padding: 5px;
		height: 25px;
	}

	#catg-images-droppable .qq-upload-drop-area {
		position: absolute;
		width: 100%;
		height: 150px;
		top: -5px;
		left: -5px;
		background: rgba(0, 0, 0, .5);
	}

	#catg-images-droppable .qq-upload-list {
		position: absolute;
		right: 5px;
		z-index: 200;
	}

	#catg-images-droppable .qq-upload-list {
		display: none !important;
	}

	#catg-images-droppable .qq-upload-list li {
		margin-top: 5px;
	}

	#catg-images-droppable .qq-upload-drop-area span {
		color: #fff;
		font-size: 20px;
		font-weight: bold;
		text-shadow: 0 0 5px #000;
	}

	#catg-images-gallery {
		min-height: 300px;
	}

	#catg-images-gallery .progress-bar {
		height:5px;
		background: #333;
	}

	#catg-images-gallery .progress-bar .bar {
		width:1%;
		height:5px;
		background: #090;
	}

	#catg-images-gallery div.gallery-item {
		position: relative;
		float: left;
		border: 1px solid #aaa;
		margin: 5px;
		background: #fff url('[{$dir_images}]pattern3.png');
		overflow: hidden;
	}

	#catg-images-gallery div.gallery-item button {
		visibility: hidden;
		position: absolute;
		top: 5px;
		right: 5px;
	}

	/* This style is necessary, because a button will be moved down and right, if clicked. */
	#catg-images-gallery div.gallery-item button.image-deleter:active {
		top: 6px;
		right: 4px;
		left: auto;
	}

	#catg-images-gallery div.gallery-item:hover button {
		visibility: visible;
	}

	#catg-images-gallery img.loader {
		position: absolute;
		left: 50%;
		top: 50%;
		margin: -8px;
	}

	#catg-images-gallery img.thumb {
		max-width: 100%;
		max-height: 100%;
		cursor: pointer;
	}
</style>