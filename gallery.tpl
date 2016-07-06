
<div class="phorts-list">
	<style scoped>
		.phorts-list {
			margin-left:-5px;
			margin-right:-5px;
		}
		.phorts-list img {
			padding:5px;
			width:20%;
		}
	</style>
	{data.info.gallery::bigimg}
</div>
<a href="/{parent.crumb}">{parent.config.title}</a>
<script>
	domready(function(){
		var div = $('.phorts-list');
		if (!div.magnificPopup) {
			console.info('Требуется magnificPopup');
			return;
		}
		div.find('a').magnificPopup({
			type: 'image',
			gallery:{
				enabled:true
			}
		});
		var hash = location.hash;
		if(hash){
			hash = hash.replace(/^#/,'');
			if (hash=='show') {
				div.find('a:first').click();
			} else {
				
				var el = document.getElementById('img-'+hash);
				$(el).click();
			}
		}
	});
</script>
{bigimg:}<a id="img-{.}" href="/{...gallerydir}{.}"><img style="width:20%" src="/-imager/?w=400&h=300&crop=1&src={...gallerydir}{.}"></a>