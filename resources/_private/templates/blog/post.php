<div class="col-lg-12 col-md-12 col-sm-12 item_news expand selected" style="display: block;">
	
	<div class="thumbnail">
		<?php the_post_thumbnail('full'); ?>
	</div>

	<div class="content_item">
		<h3>
			<span class="ico_title">
				<span class="sprite ico_topic_blog"></span>
			</span>
			<?php the_title(); ?>
		</h3>

		<p><?php the_content(); ?></p>
	</div>
</div>