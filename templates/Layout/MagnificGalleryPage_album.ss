<div class="container" id="magnific-gallery">
	<h1>$Title</h1>

	<% if not SingleAlbumView %>
	<a href="$Link" class="back-link"><%t MagnificGalleryPage.GOBACK "go back to the gallery" %></a>
	<% end_if %>

	<h2>$CurrentAlbum.AlbumName</h2>

	<div class="gallery-items gallery-grid">
		<% loop PaginatedItems %>
		<figure class="gallery-grid-item">
			<a href="$Image.Link" class="$MagnificClass" <% if IsVideo %>data-mfp-src="$VideoLink"<% end_if %>>$FormattedImage</a>
			<% if Caption %>
			<figcaption>
				$Caption
			</figcaption>
			<% end_if %>
		</figure>
		<% end_loop %>
	</div>

	<% with PaginatedItems %>
	<% if MoreThanOnePage %>
	<nav class="gallery-pagination">		
		<ul>
			<% if NotFirstPage %>
			<li class="previous"><a title="<% _t('MagnificGalleryPage.VIEWPREVIOUSPAGE','View the previous page') %>" href="$PrevLink">
					&laquo; <% _t('MagnificGalleryPage.PREVIOUS','Previous') %></a></li>
			<% else %>	
			<li class="previous-off">&laquo;<% _t('MagnificGalleryPage.PREVIOUS','Previous') %></li>
			<% end_if %>

			<% loop Pages %>
			<% if CurrentBool %>
			<li class="active">$PageNum</li>
			<% else %>
			<% if Link %>
			<li><a href="$Link" title="<% sprintf(_t('MagnificGalleryPage.VIEWPAGENUMBER','View page number %s'),$PageNum) %>">$PageNum</a></li>		
			<% else %>
			...
			<% end_if %>
			<% end_if %>
			<% end_loop %>

			<% if NotLastPage %>
			<li class="next"><a title="<% _t('MagnificGalleryPage.VIEWNEXTPAGE', 'View the next page') %>" href="$NextLink">
					<% _t('MagnificGalleryPage.NEXT','Next') %> &raquo;</a></li>
			<% else %>
			<li class="next-off"><% _t('MagnificGalleryPage.NEXT','Next') %> &raquo;</li>				
			<% end_if %>
		</ul>
	</nav> 		
	<% end_if %>
	<% end_with %>

	<% if PrevAlbum || NextAlbum %>
	<nav class="album-nav">
		<ul>

			<li class="prev">
				<% if PrevAlbum %>
				<% with PrevAlbum %>
				<div class="album-nav-img">
					<a href="$Link" title="<% sprintf(_t('MagnificGalleryPage.GOTOALBUM','Go to the %s album'),$AlbumName) %>">$CoverImage.SetWidth(100)</a>
				</div>
				<div class="album-nav-desc">
					<h3><% _t('MagnificGalleryPage.PREVIOUSALBUM','Previous Album') %></h3>							
					<h4><a href="$Link">$AlbumName</a></h4>
				</div>
				<% end_with %>
				<% end_if %>
			</li>
			<li class="next">
				<% if NextAlbum %>
				<% with NextAlbum %>
				<div class="album-nav-img">
					<a href="$Link" title="<% sprintf(_t('MagnificGalleryPage.GOTOALBUM','Go to the %s album'),$AlbumName) %>">
						$CoverImage.SetWidth(100)</a>
				</div>
				<div class="album-nav-desc">
					<h3><% _t('MagnificGalleryPage.NEXTALBUM','Next Album') %></h3>
					<h4><a href="$Link">$AlbumName</a></h4>
				</div>
				<% end_with %>
				<% end_if %>
			</li>
		</ul>
	</nav>
	<% end_if %>
</div>			