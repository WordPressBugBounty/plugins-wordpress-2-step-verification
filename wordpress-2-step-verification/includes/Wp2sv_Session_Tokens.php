<?php
class Wp2sv_Session_Tokens extends Wp2sv_Session_Tokens_Abstract {
    static function instance($user_id){
        return new static( $user_id );
    }
    /**
     * Get all sessions of a user.
     *
     * @access protected
     *
     * @return array Sessions of a user.
     */
    protected function get_sessions() {
        $sessions = Wp2sv_Model::forUser( $this->user_id)->session_tokens;

        if ( ! is_array( $sessions ) ) {
            return array();
        }

        $sessions = array_map( array( $this, 'prepare_session' ), $sessions );
        return array_filter( $sessions, array( $this, 'is_still_valid' ) );
    }

    /**
     * Converts an expiration to an array of session information.
     *
     * @param mixed $session Session or expiration.
     * @return array Session.
     */
    protected function prepare_session( $session ) {
        if ( is_int( $session ) ) {
            return array( 'expiration' => $session );
        }

        return $session;
    }

    /**
     * Retrieve a session by its verifier (token hash).
     *
     * @access protected
     *
     * @param string $verifier Verifier of the session to retrieve.
     * @return array|null The session, or null if it does not exist
     */
    protected function get_session( $verifier ) {
        $sessions = $this->get_sessions();

        if ( isset( $sessions[ $verifier ] ) ) {
            return $sessions[ $verifier ];
        }

        return null;
    }

    /**
     * Update a session by its verifier.
     *
     * @since 4.0.0
     * @access protected
     *
     * @param string $verifier Verifier of the session to update.
     * @param array  $session  Optional. Session. Omitting this argument destroys the session.
     */
    protected function update_session( $verifier, $session = null ) {
        $sessions = $this->get_sessions();

        if ( $session ) {
            $sessions[ $verifier ] = $session;
        } else {
            unset( $sessions[ $verifier ] );
        }

        $this->update_sessions( $sessions );
    }

    /**
     * Update a user's sessions in the usermeta table.
     *
     * @access protected
     *
     * @param array $sessions Sessions.
     */
    protected function update_sessions( $sessions ) {
        if ( $sessions ) {
            Wp2sv_Model::forUser( $this->user_id)->session_tokens=$sessions;
        } else {
            Wp2sv_Model::forUser( $this->user_id)->session_tokens=null;
        }
    }

    /**
     * Destroy all session tokens for a user, except a single session passed.
     *
     * @access protected
     *
     * @param string $verifier Verifier of the session to keep.
     */
    protected function destroy_other_sessions( $verifier ) {
        $session = $this->get_session( $verifier );
        $this->update_sessions( array( $verifier => $session ) );
    }

    /**
     * Destroy all session tokens for a user.
     *
     * @access protected
     */
    protected function destroy_all_sessions() {
        $this->update_sessions( array() );
    }

    /**
     * Destroy all session tokens for all users.
     *
     * @access public
     * @static
     */
    public static function drop_sessions() {
        delete_metadata( 'user', 0, Wp2sv_Model::applyPrefix('session_tokens'), false, true );
    }
}