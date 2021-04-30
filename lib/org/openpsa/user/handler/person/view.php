<?php
/**
 * @package org.openpsa.user
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

use midcom\datamanager\datamanager;

/**
 * View person class for user management
 *
 * @package org.openpsa.user
 */
class org_openpsa_user_handler_person_view extends midcom_baseclasses_components_handler
{
    /**
     * @var midcom_db_person
     */
    private $_person;

    public function _handler_view(string $guid, array &$data)
    {
        $this->_person = new midcom_db_person($guid);
        $data['view'] = datamanager::from_schemadb($this->_config->get('schemadb_person'))
            ->set_storage($this->_person);

        $this->add_breadcrumb('', $this->_person->get_label());

        $auth = midcom::get()->auth;
        if (   $this->_person->id == midcom_connection::get_user()
            || $auth->can_user_do('org.openpsa.user:manage', null, org_openpsa_user_interface::class)) {
            $buttons = [];
            $workflow = $this->get_workflow('datamanager');
            if ($this->_person->can_do('midgard:update')) {
                $buttons[] = $workflow->get_button($this->router->generate('user_edit', ['guid' => $this->_person->guid]), [
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                ]);
            }
            if ($this->_person->can_do('midgard:delete')) {
                $delete_workflow = $this->get_workflow('delete', ['object' => $this->_person]);
                $buttons[] = $delete_workflow->get_button($this->router->generate('user_delete', ['guid' => $this->_person->guid]));
            }
            if (   midcom_connection::is_user($this->_person)
                && $this->_person->can_do('midgard:privileges')) {
                $buttons[] = $workflow->get_button($this->router->generate('user_privileges', ['guid' => $this->_person->guid]), [
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("permissions"),
                    MIDCOM_TOOLBAR_GLYPHICON => 'shield',
                ]);
            }

            if ($this->_person->can_do('midgard:update')) {
                $buttons[] = $workflow->get_button($this->router->generate('person_notifications', ['guid' => $this->_person->guid]), [
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("notification settings"),
                    MIDCOM_TOOLBAR_GLYPHICON => 'bell-o',
                ]);
            }
            $this->_view_toolbar->add_items($buttons);
        }
        $this->bind_view_to_object($this->_person);

        $data['person'] = $this->_person;
        $data['account'] = new midcom_core_account($this->_person);
        return $this->show('show-person');
    }
}
