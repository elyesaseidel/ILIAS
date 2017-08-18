<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarViewGUI
{
	const CAL_PRESENTATION_DAY = 1;
	const CAL_PRESENTATION_WEEK = 2;
	const CAL_PRESENTATION_MONTH = 3;
	const CAL_PRESENTATION_AGENDA_LIST = 9;

	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $ui_factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $ui_renderer;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var integer
	 */
	protected $presentation_type;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * View initialization
	 * @param integer $a_calendar_presentation_type
	 */
	function initialize($a_calendar_presentation_type)
	{
		global $DIC;
		$this->ui_factory = $DIC->ui()->factory();
		$this->ui_renderer = $DIC->ui()->renderer();
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->tabs_gui = $DIC->tabs();
		$this->tpl = $DIC["tpl"];
		$this->toolbar = $DIC->toolbar();
		$this->presentation_type = $a_calendar_presentation_type;
	}

	/**
	 * Get app for id
	 *
	 * @param
	 * @return
	 */
	function getCurrentApp()
	{
		// @todo: this needs optimization
		$events = $this->getEvents();
		foreach ($events as $item)
		{
			if ($item["event"]->getEntryId() == (int) $_GET["app_id"])
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * Get events
	 *
	 * @param
	 * @return
	 */
	function getEvents()
	{
//		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
//		initialize($a_mode,$a_source_ref_id = 0,$a_use_cache = false)
		$schedule = new ilCalendarSchedule(new ilDate(time(),IL_CAL_UNIX),ilCalendarSchedule::TYPE_PD_UPCOMING);

		switch ($this->presentation_type)
		{
			case self::CAL_PRESENTATION_AGENDA_LIST:
				$schedule->setPeriod(new ilDate($this->seed, IL_CAL_DATE),
					new ilDate($this->period_end_day, IL_CAL_DATE));
				break;
			case self::CAL_PRESENTATION_DAY:
				$schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_DAY);
				break;
			case self::CAL_PRESENTATION_WEEK:
				$schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK);
				break;
			case self::CAL_PRESENTATION_MONTH:
				$schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH);
				break;
		}

		//return $schedule->getChangedEvents(true);

		$schedule->addSubitemCalendars(true);
		$schedule->calculate();
		$ev = $schedule->getScheduledEvents();
		return $ev;
	}


	/**
	 * Get start/end date for item
	 *
	 * @param array $item item
	 * @return array
	 */
	function getDatesForItem($item)
	{
		$start = $item["dstart"];
		$end = $item["dend"];
		if($item["fullday"])
		{
			$start = new ilDate($start, IL_CAL_UNIX);
			$end = new ilDate($end, IL_CAL_UNIX);
		}
		else
		{
			$start = new ilDateTime($start, IL_CAL_UNIX);
			$end = new ilDateTime($end, IL_CAL_UNIX);
		}
		return array("start" => $start, "end" => $end);
	}

	/**
	 * Get modal for appointment (see similar code in ilCalendarBlockGUI)
	 */
	function getModalForApp()
	{
		$f = $this->ui_factory;
		$r = $this->ui_renderer;
		$ctrl = $this->ctrl;

		// @todo: this needs optimization
		$events = $this->getEvents();

		//item => array containing ilcalendary object, dstart of the event , dend etc.
		foreach ($events as $item)
		{
			if ($item["event"]->getEntryId() == (int) $_GET["app_id"] && $item['dstart'] == (int) $_GET['dt'])
			{
				$dates = $this->getDatesForItem($item);
				// content of modal
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
				$next_gui = ilCalendarAppointmentPresentationGUI::_getInstance(new ilDate($this->seed, IL_CAL_DATE), $item);
				$content = $ctrl->getHTML($next_gui);

				if($_GET['modal_title'] != "") {
					$modal = $f->modal()->roundtrip(rawurldecode($_GET['modal_title']) ,$f->legacy($content));
				} else {
					$modal = $f->modal()->roundtrip(ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),$f->legacy($content));
				}
				echo $r->renderAsync($modal);
			}
		}
		exit();
	}

	//$a_title_forced used in plugins for rename the shy button title.
	function getAppointmentShyButton($a_calendar_entry, $a_dstart, $a_title_forced = "", $a_new_modal_title = "")
	{
		$f = $this->ui_factory;
		$r = $this->ui_renderer;

		$this->ctrl->setParameter($this, "app_id", $a_calendar_entry->getEntryId());
		$this->ctrl->setParameter($this,'dt',$a_dstart);
		$this->ctrl->setParameter($this,'seed',$this->seed->get(IL_CAL_DATE));
		if($a_new_modal_title != "")
		{
			$this->ctrl->setParameter($this,'modal_title',rawurlencode($a_new_modal_title));
		}
		$url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
		$this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
		$this->ctrl->setParameter($this, "dt", $_GET["dt"]);
		$this->ctrl->setParameter($this,'seed',$_GET["seed"]);
		$this->ctrl->setParameter($this,'modal_title',$_GET["modal_title"]);

		$modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

		$title = ($a_title_forced == "")? $a_calendar_entry->getPresentationTitle() : $a_title_forced;

		$comps = [$f->button()->shy($title, "")->withOnClick($modal->getShowSignal()), $modal];

		return $r->render($comps);
	}

	public function getAgendaShyButton()
	{

	}

	//get active plugins.
	public function getActivePlugins($a_slot_id)
	{
		global $ilPluginAdmin;

		$res = array();

		foreach($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Calendar", $a_slot_id) as $plugin_name)
		{
			$res[] = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE,
				"Calendar", $a_slot_id, $plugin_name);
		}

		return $res;
	}

	public function getModalTitleByPlugins()
	{
		$modal_title = "";
		//demo of plugin execution.
		//"capm" is the plugin slot id for Appointment presentations (modals)
		foreach($this->getActivePlugins("capm") as $plugin)
		{
			$modal_title = ($new_title = $plugin->editModalTitle())? $new_title : "";
		}
		return $modal_title;
	}

	/**
	 * @param $a_cal_entry
	 * @param $a_start_date
	 * @param $a_title
	 * @return string
	 */
	public function getContentByPlugins($a_cal_entry, $a_start_date, $a_title)
	{
		$content = $a_title;
		//"capg" is the plugin slot id for AppointmentCustomGrid
		foreach($this->getActivePlugins("capg") as $plugin)
		{
			$plugin->setAppointment($a_cal_entry, new ilDateTime($a_start_date));
			if($new_content = $plugin->replaceContent())
			{
				$content = $new_content;
			}
			else
			{
				$shy_title = ($new_title = $plugin->editShyButtonTitle())? $new_title : "";
				$content = $this->getAppointmentShyButton($a_cal_entry, $a_start_date, $shy_title);

				if($glyph = $plugin->addGlyph())
				{
					$content = $glyph." ".$content;
				}

				if($more_content = $plugin->addExtraContent())
				{
					$content .= " ".$more_content;
				}
			}
		}

		return $content;
	}

	/**
	 * Add download link to toolbar
	 *
	 * //TODO rename this method to something like addToolbarDonwloadFiles
	 * @param
	 * @return
	 */
	function addToolbarActions()
	{
		$settings = ilCalendarSettings::_getInstance();
		if($settings->isBatchFileDownloadsEnabled())
		{
			$toolbar = $this->toolbar;
			$f = $this->ui_factory;
			$lng = $this->lng;
			$ctrl = $this->ctrl;

			// file download
			$add_button = $f->button()->standard($lng->txt("cal_download_files"),
				$ctrl->getLinkTarget($this, "downloadFiles"));
			$toolbar->addSeparator();
			$toolbar->addComponent($add_button);
		}
	}

	/**
	 * Download files
	 */
	function downloadFiles()
	{
		include_once("./Services/Calendar/classes/FileHandler/class.ilCalendarFileHandler.php");
		$file_handler = new ilCalendarFileHandler();
		$file_handler->setEvents($this->getEvents());
		$file_handler->run();
		
	}


}