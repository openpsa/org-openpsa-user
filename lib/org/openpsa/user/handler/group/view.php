<?php
/**
 * @package org.openpsa.user
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

use midcom\datamanager\datamanager;

/**
 * View group class for user management
 *
 * @package org.openpsa.user
 */
class org_openpsa_user_handler_group_view extends midcom_baseclasses_components_handler
{
    /**
     * @var midcom_db_group
     */
    private $_group;

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    private function load_datamanager() : datamanager
    {
        $dm = datamanager::from_schemadb($this->_config->get('schemadb_group'));
        $dm->get_schema('default')->get_field('persons')['hidden'] = true;
        return $dm->set_storage($this->_group);
    }

    public function _handler_view(string $guid, array &$data)
    {
        midcom::get()->auth->require_user_do('org.openpsa.user:access', null, org_openpsa_user_interface::class);

        $this->_group = new midcom_db_group($guid);
        $data['group'] = $this->_group;

        $data['view'] = $this->load_datamanager();

        $this->add_breadcrumb('', $this->_group->get_label());

        $workflow = $this->get_workflow('datamanager');
        $buttons = [];
        if ($this->_group->can_do('midgard:update')) {
            $buttons[] = $workflow->get_button($this->router->generate('group_edit', ['guid' => $guid]), [
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ]);
        }
        if ($this->_group->can_do('midgard:delete')) {
            $delete_workflow = $this->get_workflow('delete', ['object' => $this->_group]);
            $buttons[] = $delete_workflow->get_button($this->router->generate('group_delete', ['guid' => $guid]));
        }

        if ($this->_group->can_do('midgard:privileges')) {
            $buttons[] = $workflow->get_button($this->router->generate('group_privileges', ['guid' => $guid]), [
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("permissions"),
                MIDCOM_TOOLBAR_GLYPHICON => 'shield',
            ]);
        }

        if ($this->_group->can_do('midgard:update') && midcom::get()->componentloader->is_installed('org.openpsa.notifications')) {
            $buttons[] = $workflow->get_button($this->router->generate('group_notifications', ['guid' => $guid]), [
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("notification settings"),
                MIDCOM_TOOLBAR_GLYPHICON => 'bell-o',
            ]);
        }

        if (midcom::get()->auth->can_user_do('midgard:create', null, midcom_db_person::class)) {
            $buttons[] = $workflow->get_button($this->router->generate('user_create_group', ['guid' => $guid]), [
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                MIDCOM_TOOLBAR_GLYPHICON => 'user',
            ]);
        }
        $this->_view_toolbar->add_items($buttons);
        $this->bind_view_to_object($this->_group);
        midcom::get()->head->set_pagetitle($this->_group->get_label());

        return $this->show('show-group');
    }
}
