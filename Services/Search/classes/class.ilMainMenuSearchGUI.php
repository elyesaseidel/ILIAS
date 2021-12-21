<?php declare(strict_types=1);

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Add a search box to main menu
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 *
 * @ingroup ServicesSearch
 */
class ilMainMenuSearchGUI
{
    protected ?ilTemplate $tpl = null;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    
    private int $ref_id;
    private int $obj_id = 0;
    private string $type = '';
    private bool $isContainer = true;
    
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        
        
        if (isset($_GET['ref_id'])) {
            $this->ref_id = (int) $_GET['ref_id'];
        } else {
            $this->ref_id = ROOT_FOLDER_ID;
        }
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);

        $this->lng->loadLanguageModule("search");
        
    }

    public function getHTML() : string
    {
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();
        $this->tpl = new ilTemplate('tpl.main_menu_search.html', true, true, 'Services/Search');
        
        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            $this->tpl->setVariable('LABEL_SEARCH_OPTIONS', $this->lng->txt("label_search_options"));
            if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled() or (int) $_GET['ref_id']) {
                $this->tpl->setCurrentBlock("position");
                $this->tpl->setVariable('TXT_GLOBALLY', $this->lng->txt("search_globally"));
                $this->tpl->setVariable('ROOT_ID', ROOT_FOLDER_ID);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("position_hid");
                $this->tpl->setVariable('ROOT_ID_HID', ROOT_FOLDER_ID);
                $this->tpl->parseCurrentBlock();
            }
            if (isset($_GET['ref_id']) && (int) $_GET['ref_id']) {
                $this->tpl->setCurrentBlock('position_rep');
                $this->tpl->setVariable('TXT_CURRENT_POSITION', $this->lng->txt("search_at_current_position"));
                $this->tpl->setVariable('REF_ID', (int) $_GET["ref_id"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->user->getId() != ANONYMOUS_USER_ID && ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tpl->setCurrentBlock('usr_search');
            $this->tpl->setVariable('TXT_USR_SEARCH', $this->lng->txt('search_users'));
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable(
            'FORMACTION',
            $this->ctrl->getFormActionByClass(
                ilSearchControllerGUI::class,
                'remoteSearch'
            )
        );
        $this->tpl->setVariable('BTN_SEARCH', $this->lng->txt('search'));
        $this->tpl->setVariable('SEARCH_INPUT_LABEL', $this->lng->txt('search_field'));
        $this->tpl->setVariable('AC_DATASOURCE',
            $this->ctrl->getLinkTargetByClass(
                ilSearchControllerGUI::class,
                'autoComplete',
                null,
                true
            )
        );

        $this->tpl->setVariable('IMG_MM_SEARCH', ilUtil::img(
            ilUtil::getImagePath("icon_seas.svg"),
            $this->lng->txt("search")
        ));

        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            $this->tpl->setVariable(
                'HREF_SEARCH_LINK',
                'ilias.php?basClass=' . ilSearchControllerGUI::class
            );
            $this->tpl->setVariable('TXT_SEARCH_LINK', $this->lng->txt("last_search_result"));
        }
        // #10555 - we need the overlay for the autocomplete which is always active
        $this->tpl->setVariable('TXT_SEARCH', $this->lng->txt("search"));
        $ov = new ilOverlayGUI("mm_search_menu");
        //$ov->setTrigger("main_menu_search", "none",
        //	"main_menu_search", "tr", "br");
        //$ov->setAnchor("main_menu_search", "tr", "br");
        $ov->setAutoHide(false);
        $ov->add();
        
        return $this->tpl->get();
    }
}
