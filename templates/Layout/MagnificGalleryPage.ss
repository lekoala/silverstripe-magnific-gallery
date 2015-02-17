<div class="container" id="magnific-gallery">
	<h1>$Title</h1>
	$Content

	<% if Albums %>
	<div class="albums-list">
		<div class="magnific-grid">
			<% loop Albums %>
			<figure class="effect-$Effect">
				<% if CoverImage %>
				<% with FormattedCoverImage %>
				<img src="$URL" alt="$Title" />
				<% end_with %>
				<% else %>
				<div class="no-image" style="width:{$CoverWidth}px;height:{$CoverHeight}px"></div>
				<% end_if %>
				<figcaption>
					<div>
						<h2>$AlbumName</h2>
						<p class="description">$Description.LimitWordCount(60)</p>
					</div>
					<a href="$Link">View more</a>
				</figcaption>         
			</figure>
			<% end_loop %>
		</div>
	</div>
	<% else %>
	<p><%t MagnificGalleryPage.SORRYNOALBUMS "Sorry there are no albums available" %></p>
	<% end_if %>
</div>			