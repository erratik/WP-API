# Documentation

## Channels

**Channels** are the items that appear on header of the front page.
The sources attached to these channels feed the channels with the videos they import from video services, through the `WPVR Video Robot` plugin.

Channels can be:

- Enabled: Show or hide the channel in the front page.
- Featured: Feature videos in the header of the front page.

### Actions

- Add a channel
- Manage channel
  - Sources for channel
  - Videos in channel
    - Search videos
    - Batch publishing status changes
  - Display options _(thumbnail, name)_ \*

## Sources

**Sources** are used by the `WPVR Video Robot` plugin. They define the service video sources to fetch from. Please refer to the plugin's documentation to know more about how they are used.

Although sources can be run from the _Sources tab_ in the WP Dashboard, it's recommended to do this from the channel's **Videos** tab as the videos get imported automatically.

- Sources can only be attached to one channel at a time right now
- Attaching a source to a channel puts the videos form that source into that channel
- `WPVR Video Robot` plugin creates a set of metadata in the `wp_wpvr_source_meta` table, related to the `wpvr_source` post ID, from in the `wp_posts` table

## Videos

Videos are fetched by the `WPVR Video Robot` plugin. To appear on the UI in the right channel, they must both exist as:

- `wpvr_video`: an entry in the `wp_posts` table
- `snaptube vid` : an entry in the `wp_hdflvvideoshare` table
  - `WPVR Video Robot` plugin creates a set of metadata in the `wp_wpvr_video_meta` table, related to the `vid`

Updating video status in the videos tab of a channel will:

- Create a new post of type `videogallery` to be displayed in the channel, with `[hdvideo=$vid]` content
- Add a new entry in the `wp_hdflvvideoshare` table, using the newly created post's ID as a reference, in the `slug` column

---

## To do

1. Trashing videos from the channel's **Videos** tab will mark the videos as _unwanted_. They can then be restored from the Unwanted Videos page in the `WPVR Video Robot` plugin.
2. Restore display options to change thumbnail and name
