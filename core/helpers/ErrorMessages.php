<?php

class OrbtrErrorMessages
{
	const PREFIX = "orbtrerrors_";
	
	public function __construct()
	{
		// Initialize variables
		$defaultOptions         = array( 'updates' => array(), 'errors' => array() );
	
		/* HKFIX: array_merge was overwriting the values read from get_option, 
		 * moved $defaultOptions as first argument to array_merge */
		$this->options          = array_merge( $defaultOptions, get_option( self::PREFIX . 'options', array() ) );
		$this->updatedOptions   = false;
	
		/* HKFIX: the count for update and error messages was hardcoded,
		 * which was ignoring the messages already in the options table read above
		 * later in print the MessageCounts is used in loop
		 * So I updated to set the count based on the options read from get_option */
		$this->userMessageCount = array();
		foreach ( $this->options as $msg_type => $msgs ) {
			$this->userMessageCount[$msg_type] = count( $msgs );
		}
		// more
	
		add_action( 'admin_notices',    array($this, 'printMessages') );
	
		// does other stuff
	}

	/**
	 * Displays updates and errors
	 * @author Ian Dunn <redacted@mpangodev.com>
	 */
	public function printMessages()
	{
	
		foreach( array('updates', 'errors') as $type )
		{
			if( $this->options[$type] && ( $this->userMessageCount[$type] ) )
			{
				echo '<div id="message" class="'. ( $type == 'updates' ? 'updated' : 'error' ) .'">';
				foreach($this->options[$type] as $message)
					if( $message['mode'] == 'user' || self::DEBUG_MODE )
						echo '<p>'. $message['message'] .'</p>';
				echo '</div>';
	
				$this->options[$type] = array();
				$this->updatedOptions = true;
				$this->userMessageCount[$type] = 0;
	
			}
		}
	
		/* HKFIX: Save the messages, can't wait for destruct */
		if ( $this->updatedOptions ) {
			$this->saveMessages();
		}
	
	}
	
	/**
	 * Queues up a message to be displayed to the user
	 * @author Ian Dunn <redacted@mpangodev.com>
	 * @param string $message The text to show the user
	 * @param string $type 'update' for a success or notification message, or 'error' for an error message
	 * @param string $mode 'user' if it's intended for the user, or 'debug' if it's intended for the developer
	 */
	public function enqueueMessage($message, $type = 'update', $mode = 'user')
	{
	
		array_push($this->options[$type .'s'], array(
			'message' => $message,
			'type' => $type,
			'mode' => $mode
		) );
	
	
		if($mode == 'user')
			$this->userMessageCount[$type . 's']++;
	
		/* HKFIX: save the messages, can't wait for destruct */
		$this->saveMessages();
	}
	
	/* HKFIX: Dedicated funciton to save messages 
	 * Can also be called from destruct if that is really required */
	public function saveMessages() 
	{
		update_option(self::PREFIX . 'options', $this->options);
	}	
}