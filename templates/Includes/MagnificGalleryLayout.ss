<div class="container" id="magnific-gallery">
	<h1>$Title</h1>
	$Content

	<% if Albums %>
	<div id="magnific-gallery-album-list">
		<div class="magnific-grid">
			<% loop Albums %>
			<figure class="effect-$Effect">
				<% if CoverImage %>
				<% with FormattedCoverImage %>
				<img src="$URL" alt="$Title" />
				<% end_with %>
				<% else %>
				<span class="no-image"></span>
				<% end_if %>
				<figcaption>
					<div>
						<h2>$AlbumName</h2>
						<p>$Description.LimitWordCount(60)</p>
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