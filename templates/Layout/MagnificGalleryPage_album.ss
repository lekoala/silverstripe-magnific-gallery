<div class="container" id="magnific-gallery">
	<h1>$Title</h1>

	<% if not SingleAlbumView %>
	<a href="$Link" class="back-link"><%t MagnificGalleryPage.GOBACK "go back to the gallery" %></a>
	<h2>$CurrentAlbum.AlbumName</h2>
	<% end_if %>

	<div class="gallery-items" id="gallery-album">
		<% loop PaginatedItems %>
		<figure class="item">
			<a href="$Image.Link" class="$MagnificClass" <% if IsVideo %>data-mfp-src="$VideoLinkAutoplay"<% end_if %>>$FormattedImage</a>
			<% if Caption %>
			<figcaption>
				$Caption
			</figcaption>
			<% end_if %>
		</figure>
		<% end_loop %>
	</div>

	<% include MagnificPagination %>
	<% include MagnificPrevNext %>
</div>			