WordCamp Style Import
=====================

Import the styles from another site on the WordCamp network. Assumes the WordCamp.org environment, which can be partially replicated with the [WordPress Meta Environment](https://github.com/iandunn/wordpress-meta-environment) vagrant.

- Go to "Appearance > Import Style", and you're given a list of all the wordcamps on the network to import from.
	- Technically, all WordCamps with wc posts on central.wc (the schedule) (this should be all WCs)
	- Uses mshots to generate the WC preview image, but could maybe be another image attached to the central.wc post
	- Currently no caching whatsoever, so it's a pretty slow page
- From here, you can click "Import" or "Live Preview"
	- __If we click Live Preview__ on WC Chicago 2014, we'll get the customizer preview with Twenty Thirteen and the custom CSS from Chicago's site
	- On save, we'll activate Twenty Thirteen and copy over the Custom CSS
		- The preview will show our content: so if Chicago used a page on front and we have a post list, we'll still have a post list-- the preview might not immediately match 1:1
		- Widgets are also not copied over
		- Most images in Custom CSS are full URLs, so we shouldn't see any broken images, but it's possible.
		- Not sure if anyone can/does use typekit for fonts, but is tied to that organizer's account, and could have a URL restriction
  - __If we click Import__ we don't need to preview and we're using the same theme as the source, we can directly import the CSS from the other site
    - This button should be disabled if the themes don't match.
    - This does not apply automatically, it just loads the CSS into the CSS editor, and it's up to you to edit, preview, and save.

![Import style list](https://cldup.com/fF8udWDzgv.png)
