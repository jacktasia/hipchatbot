HipChatBot
==========

The HipChatBot class allows you to easily create custom bots for your HipChat rooms. Take a look at the `run_hipchatbot.php` file to see an example of how to create & run your own bot. You can register multiple bots for a HipChat room.

Bot Types
---------

There's two types of bots (so far) you can register a `name` bot or a `needle` bot.

A `needle` bot will look for the "needle" you give it in every HipChat message (or "haystack") if it is found then the function assigned to that needle will run. Whatever is returned by this function is what will be sent back to room it is watching as a message. 

Here's an example that will look for the string "emacs" and when found a user named "Jack" will respond with "yes! e m a c s was mentioned" ...note that "emacs" is spaced out. All messages sent by the HipChatBot will be "cleaned" of all registered needles (so you don't get in an infinite loop).

<pre>
$hcb = new HipChatBot(API_KEY,ROOM_ID);
$hcb->register_needle_bot('emacs', 'Jack', function($args) {
	return 'yes! emacs was mentioned';
});
// more bots can be registered before calling run()
$hcb->run();
</pre>


--------

A `name` bot will look for its name after the special character `:` after the very beginning of a message

So `:bot weather` would be accesed like so:
<pre>
$hcb->register_name_bot('bot', 'Bot', function($args) {
	if ( isset($args[0]) && $args[0] == 'weather' ) {
		// do some api call to get weather info or something ( see run_hipchatbot.php )
		return 'here is some information about the weather';
	}
});
</pre>
 

Requires
--------

* HipChat Account (with API token and Room ID)
* PHP 5.3 with curl support.
