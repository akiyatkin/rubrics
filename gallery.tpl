
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
<script type="module">
	import { CDN } from '/vendor/akiyatkin/load/CDN.js'
	(async () => {
		let div = document.getElementById('{div}')
		await CDN.load('magnific-popup')
		
		$(div).find('a.gallery').magnificPopup({
			type: 'image',
			gallery:{
				enabled:true
			}
		})
		var hash = location.hash
		if (hash) {
			hash = hash.replace(/^#/, '')
			if (hash == 'show') {
				div.find('a:first').click()
			} else {
				var el = document.getElementById('img-'+hash)
				$(el).click()
			}
		}
	})()
</script>
{bigimg:}<a class="gallery" id="img-{name}" href="/-imager/?src={...gallerydir}{~encode(file)}"><img style="width:20%" src="/-imager/?w=400&h=300&crop=1&src={~encode(...gallerydir)}{~encode(file)}&top=1"></a>
