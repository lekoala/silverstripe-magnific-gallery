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