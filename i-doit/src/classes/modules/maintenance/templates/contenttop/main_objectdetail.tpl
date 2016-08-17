<script type="text/javascript">
	(function () {
		'use strict';

		$('contentTopTitle')
			.insert(new Element('span', {id: 'maintenance_head', className:'pl15 grey'})
				.update(new Element('img', {src:'[{$dir_images}]icons/silk/wrench.png', className:'mr5 vam'}))
				.insert('[{isys type="lang" ident="LC__MAINTENANCE__OBJECT_IN_MAINTENANCE"}]'));
	})();
</script>