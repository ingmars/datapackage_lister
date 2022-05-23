


<div class="clear"></div>


<!-- You can start editing here. -->


<!-- If comments are closed. -->
<!--<p class="nocomments">Comments are closed.</p>-->





</div>
</div><!-- .post -->


</div>
</div><!-- #content -->

</div><!-- #container -->


<div id="scroll-top">
	<a href="#top"><span></span></a>
</div>

<div class="clear"></div>

<footer role="contentinfo" class="content">

	<div id="footer-widgets" class="clearfix content">

		<div class="row">

			<div id="footer1" class="sidebar four columns" role="complementary">


				<div id="nav_menu-3" class="widget widget_nav_menu"><div class="menu-impressum-container"><ul id="menu-impressum" class="menu"><li id="menu-item-130" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-130"><a href="http://open-power-system-data.org/about/">About</a></li>
						</ul></div></div>

			</div>
			<div id="footer2" class="sidebar four columns" role="complementary">


			</div>
			<div id="footer3" class="sidebar four columns last" role="complementary">


			</div>

		</div>

	</div> <!-- end #footer widgets-->

	<div id="footer-copy">

		<div class="row">

		</div>
	</div>




</footer> <!-- end footer -->



<!--[if lt IE 7 ]>
<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
<![endif]-->



<script type="text/javascript">


	/*-----------------------------------------------------------------------------------

	 AJAX Page Loading

	 -----------------------------------------------------------------------------------*/
	jQuery(document).ready(function($) {

		// If the link is on site, load it via AJAX
		$('#portfolio-container .portfolio-link').live('click', function(e) {
			e.preventDefault();
			var dataSlide = $(this).attr('data-slide');
			var targetDiv;
			var header = $('header[role="banner"]');
			var offsetHeight = -50;
			if (header.css('position') == 'absolute' || header.css('display') == 'none') { offsetHeight = 0; };
			if($(this).hasClass('portfolio-link')) {
				var portfolio = true;
				targetDiv = $('#portfolio-loader');
				targetDiv.slideUp(300);
			}

			var path = $(this).attr('href');
			var title = $(this).text();
			targetDiv.find('.content').load(path + ' #content', {limit: 25}, function(responseText, textStatus, req) {

				if (textStatus == "error") {
					return "It seems we've encountered an error...";
				}

				/*-----------------------------------------------------------------------------------

				 Toggles & Accordions (AJAX)

				 -----------------------------------------------------------------------------------*/

				$(function(){ // run after page loads
					$(".toggle_container").hide();
					//Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
				});

				jQuery(".accordion").accordion()


				/*-----------------------------------------------------------------------------------

				 Tabs (AJAX)

				 -----------------------------------------------------------------------------------*/

				$('ul.tabs').each(function(i) {
					//Get all tabs
					var tab = $(this).find('> li > a');
					$(this).find('li:first').addClass("active").fadeIn('fast'); //Activate first tab
					$(this).find("li:first a").addClass("active").fadeIn('fast'); //Activate first tab
					$(this).next().find("li:first").addClass("active").fadeIn('fast'); //Activate first tab

					tab.click(function(e) {

						//Get Location of tab's content
						var contentLocation = $(this).attr('href') + "Tab";

						//Let go if not a hashed one
						if(contentLocation.charAt(0)=="#") {

							e.preventDefault();

							//Make Tab Active
							tab.parent().removeClass('active');
							$(this).parent().addClass('active');

							//Show Tab Content & add active class
							$(contentLocation).show().addClass('active').siblings().hide().removeClass('active');

						}
					});
				});



				/*-----------------------------------------------------------------------------------

				 Sliders (AJAX)

				 -----------------------------------------------------------------------------------*/




				/*-----------------------------------------------------------------------------------

				 PrettyPhoto (AJAX)

				 -----------------------------------------------------------------------------------*/
				<!-- Makes all photos use PrettyPhoto -->
				/*$("a[href$='.jpg'], a[href$='.jpeg'], a[href$='.gif'], a[href$='.png']").prettyPhoto({
				 overlay_gallery: false /* If set to true, a gallery will overlay the fullscreen image on mouse over
				 });*/
				$("a[rel^='prettyPhoto']").prettyPhoto({
					overlay_gallery: false /* If set to true, a gallery will overlay the fullscreen image on mouse over */
				});


				targetDiv.hideLoading();
				targetDiv.height('auto');
				if(portfolio) {
					targetDiv.slideToggle(1000);
					offsetHeight = offsetHeight * 2;
					jQuery.scrollTo.window().queue([]).stop(); // Prevent scroll queue from building up
					jQuery(window).scrollTo(targetDiv, {duration:1600, easing:'swing', offset: offsetHeight, axis:'y'}, {queue:false});
				}
				portfolio = false;
			});
		});
	});


	jQuery(document).ready(function($){

		// hide #back-top first
		$("#scroll-top").hide();


		// fade in #back-top
		$(function () {
			$(window).scroll(function () {
				if ($(this).scrollTop() > 100) {
					$('#scroll-top').fadeIn();
				} else {
					$('#scroll-top').fadeOut();
				}
			});

			// scroll body to 0px on click
			$('#scroll-top a').click(function () {
				$('body,html').animate({
					scrollTop: 0
				}, 1800);
				return false;
			});
		});

		// close portfolio button
		$('#portfolio-close').click(function () {
			$('#portfolio-loader').slideUp(600);
			return false;
		});
		// close portfolio button
		$('#dynamic-close').click(function () {
			$('#dynamic').slideUp(600);
			return false;
		});

		//  Add active class to current page or section
		if( $("html").is(".ie8, .ie7") ) {}
		else {
			jQuery(window).scroll(function () {
				var inview = $('.container:in-viewport:first').attr('id'),
					$link = $('#nav li a').filter('[data-slide=' + inview + ']');

				if ($link.length && !$link.parent().is('.active')) {
					$('#nav li').removeClass('active');
					$link.parent().addClass('active');
					$link.parent().parents('li').addClass('active');
				}
			});
			jQuery(window).scroll();
		}



	});


	/*-----------------------------------------------------------------------------------

	 AJAX Page Loading

	 -----------------------------------------------------------------------------------*/




	/*-----------------------------------------------------------------------------------

	 PrettyPhoto

	 -----------------------------------------------------------------------------------*/
	jQuery(document).ready(function($) {
		<!-- Uncomment below to make all photos use PrettyPhoto -->
		/*$("a[href$='.jpg'], a[href$='.jpeg'], a[href$='.gif'], a[href$='.png']").prettyPhoto({
		 overlay_gallery: false /* If set to true, a gallery will overlay the fullscreen image on mouse over
		 });*/
		$("a[rel^='prettyPhoto']").prettyPhoto({
			deeplinking:false,
			overlay_gallery: false /* If set to true, a gallery will overlay the fullscreen image on mouse over */
		});
	})



	jQuery(document).ready(function($) {


		/*-----------------------------------------------------------------------------------

		 Toggles & Accordions

		 -----------------------------------------------------------------------------------*/

		jQuery(".accordion").accordion()

		$(function(){ // run after page loads
			$(".toggle_container").hide();
			//Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
			$("p.trigger").live('click', function(){
				$(this).toggleClass("active").next().slideToggle("normal");
				return false; //Prevent the browser jump to the link anchor
			});
		});


		/*-----------------------------------------------------------------------------------

		 Tabs

		 -----------------------------------------------------------------------------------*/

		$('ul.tabs').each(function(i) {
			//Get all tabs
			var tab = $(this).find('> li > a');
			$(this).find('li:first').addClass("active").fadeIn('fast'); //Activate first tab
			$(this).find("li:first a").addClass("active").fadeIn('fast'); //Activate first tab
			$(this).next().find("li:first").addClass("active").fadeIn('fast'); //Activate first tab

			tab.click(function(e) {

				//Get Location of tab's content
				var contentLocation = $(this).attr('href') + "Tab";

				//Let go if not a hashed one
				if(contentLocation.charAt(0)=="#") {

					e.preventDefault();

					//Make Tab Active
					tab.parent().removeClass('active');
					$(this).parent().addClass('active');

					//Show Tab Content & add active class
					$(contentLocation).show().addClass('active').siblings().hide().removeClass('active');

				}
			});
		});

	});  //End Document Ready


	/*-----------------------------------------------------------------------------------

	 Sliders

	 -----------------------------------------------------------------------------------*/



	/*-----------------------------------------------------------------------------------

	 Responsive Navigation

	 -----------------------------------------------------------------------------------*/
	jQuery(window).load(function($) {
		jQuery("[role='navigation']").flexNav();
	});



	/*-----------------------------------------------------------------------------------

	 Portfolio Filtering

	 -----------------------------------------------------------------------------------*/
	jQuery(document).ready(function($) {
		$.Isotope.prototype._getCenteredMasonryColumns = function() {
			this.width = this.element.width();

			var parentWidth = this.element.parent().width();

			// i.e. options.masonry && options.masonry.columnWidth
			var colW = this.options.masonry && this.options.masonry.columnWidth ||
					// or use the size of the first item
				this.$filteredAtoms.outerWidth(true) ||
					// if there's no items, use size of container
				parentWidth;

			var cols = Math.floor( parentWidth / colW );
			cols = Math.max( cols, 1 );

			// i.e. this.masonry.cols = ....
			this.masonry.cols = cols;
			// i.e. this.masonry.columnWidth = ...
			this.masonry.columnWidth = colW;
		};

		$.Isotope.prototype._masonryReset = function() {
			// layout-specific props
			this.masonry = {};
			// FIXME shouldn't have to call this again
			this._getCenteredMasonryColumns();
			var i = this.masonry.cols;
			this.masonry.colYs = [];
			while (i--) {
				this.masonry.colYs.push( 0 );
			}
		};

		$.Isotope.prototype._masonryResizeChanged = function() {
			var prevColCount = this.masonry.cols;
			// get updated colCount
			this._getCenteredMasonryColumns();
			return ( this.masonry.cols !== prevColCount );
		};

		$.Isotope.prototype._masonryGetContainerSize = function() {
			var unusedCols = 0,
				i = this.masonry.cols;
			// count unused columns
			while ( --i ) {
				if ( this.masonry.colYs[i] !== 0 ) {
					break;
				}
				unusedCols++;
			}

			return {
				height : Math.max.apply( Math, this.masonry.colYs ),
				// fit container to columns that have been used;
				width : (this.masonry.cols - unusedCols) * this.masonry.columnWidth
			};
		};







		(function() {

			var $container = $('#portfolio-container');

			$(window).on('load', function() {
				// initialize Isotope
				$container.isotope({
					// options...
					resizable: false // disable normal resizing

				});
			});

			// update columnWidth on window resize
			$(window).smartresize(function(){
				$container.isotope({
					// update columnWidth to a percentage of container width

				});
			});

			// filter items when filter link is clicked
			$('#filter a').click(function(){
				var selector = $(this).attr('data-filter');
				$('#portfolio-container .isotope-item a:first-child').attr('rel', 'prettyPhoto[gallery]');
				$('#portfolio-container ' + selector + ' a:first-child').attr('rel', 'prettyPhoto[active]');
				$container.isotope({
					filter: selector,
					animationOptions: {
						duration: 750,
						easing: 'linear',
						queue: false
					}
				});
				return false;
			});

		})();

	});

	/*-----------------------------------------------------------------------------------

	 Portfolio Captions

	 -----------------------------------------------------------------------------------*/

	jQuery(document).ready(function($) {
		$(document).ready(function(){
			$('.caption').hide();
			$('#portfolio-container .element').hover(function () {
					$('.caption', this).stop().fadeTo('slow', 1.0);
				},
				function () {
					$('.caption', this).stop().fadeTo('slow', 0);
				});
		});
	});


	/*-----------------------------------------------------------------------------------

	 Custom User JS

	 -----------------------------------------------------------------------------------*/


</script>
<script type='text/javascript'>
	var colomatduration = 'fast';
	var colomatslideEffect = 'slideFade';
</script><script type='text/javascript' src='http://open-power-system-data.org/wp-content/plugins/jquery-collapse-o-matic/js/collapse.js?ver=1.5.18'></script>
<script type='text/javascript'>
	/* <![CDATA[ */
	var tocplus = {"smooth_scroll":"1","smooth_scroll_offset":"100"};
	/* ]]> */
</script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/plugins/table-of-contents-plus/front.min.js?ver=1509'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.scrollTo.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.easing.1.3.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.showLoading.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.prettyPhoto.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-includes/js/jquery/ui/core.min.js?ver=1.11.4'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-includes/js/jquery/ui/widget.min.js?ver=1.11.4'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-includes/js/jquery/ui/tabs.min.js?ver=1.11.4'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-includes/js/jquery/ui/accordion.min.js?ver=1.11.4'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.flexslider.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.flexnav.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.isotope.min.js?ver=4.2.3'></script>
<script type='text/javascript' src='http://open-power-system-data.org/wp-content/themes/quickstep//js/jquery.viewport.mini.js?ver=4.2.3'></script>

</body>

</html>