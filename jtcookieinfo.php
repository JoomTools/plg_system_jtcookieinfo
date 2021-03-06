<?php
/**
 * Plugin for Joomla! 3.6.4 and higher
 *
 * Displays a pop-up window with information about the use of cookies.
 *
 * @package      Joomla.Plugin
 * @subpackage   System.jtcookieinfo
 * @author       Guido De Gobbis <guido.de.gobbis@joomtools.de>
 * @copyright    2015 JoomTools
 * @license      GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
 * @link         http://joomtools.de
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Class PlgSystemJtcookieinfo
 *
 * Displays a pop-up window with information about the use of cookies.
 *
 * @package      Joomla.Plugin
 * @subpackage   System.jtcookieinfo
 * @since        2.5
 */
class PlgSystemJtcookieinfo extends JPlugin
{
	protected $jtci;
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   3.0.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * Global application object
	 *
	 * @var     JApplication
	 * @since   3.0.0
	 */
	protected $app = null;

	/**
	 * onBeforeRender
	 *
	 * @return   void
	 * @since    2.5
	 */
	public function onBeforeRender()
	{
		$position   = '';
		$padding    = "$('body').css({'padding-top' : bodycss});";
		$paddingnew = "
				var bodycss = $('body').css('padding-top');
				var newbodycss = parseInt(bodycss) + $('.jtci').height();
				$('body').css({'padding-top' : newbodycss});
			";

		if ($this->app->isAdmin())
		{
			return;
		}

		$cookie = $this->app->input->cookie->getBool('jtci_accept', false);

		if ($this->params->get('jtci_set_position', 'top') == 'bottom')
		{
			$position   = '.css({top:"inherit",bottom:0})';
			$paddingnew = "
					var bodycss = $('body').css('padding-bottom');
					var newbodycss = parseInt(bodycss) + $('.jtci').height();
					$('body').css({'padding-bottom' : newbodycss}).delay(800).show('slow');
				";
			$padding    = "$('body').css({'padding-bottom' : bodycss});";
		}

		$script = '
			jQuery(function ($) {
				$(".jtci").hide();
				if (Cookies.get("jtci_accept") == undefined) {
					$(".jtci")' . $position . '.delay(800).show("slow");
					' . $paddingnew . '
				}
				$(".jtci [data-dismiss=\'alert\']").each( function(){
					$(this).on("click", function () {
						Cookies.set("jtci_accept", true, {expires: ' . (int) $this->params->get('jtci_expire', '365') . ', path: "' . JURI::root(true) . '"});
						$(".jtci").hide("slow");
						' . $padding . '
					});
				});
			});
		';

		if ($cookie === false)
		{
			$document = JFactory::getDocument();
			$min      = (JDEBUG) ? '' : '.min';

			JHtml::_('script', 'plugins/system/jtcookieinfo/assets/jquery.cookie' . $min . '.js');
			JHtml::_('stylesheet', 'plugins/system/jtcookieinfo/assets/jtcookieinfo' . $min . '.css');
			$document->addScriptDeclaration($script);
		}
	}

	/**
	 * onAfterRender
	 *
	 * @return   void
	 * @since    2.5
	 */
	public function onAfterRender()
	{
		if ($this->app->isAdmin())
		{
			return;
		}

		$this->loadLanguage('plg_system_jtcookieinfo');

		$jtci        = new stdClass;
		$messageType = array(
			'bs3'   => array(
				'error' => 'danger',
			),
			'uikit' => array(
				'info'  => '',
				'error' => 'danger',
			),
		);

		$jtci->theme      = $this->params->get('jtci_theme', 'default');
		$jtci->setTitle   = $this->params->get('jtci_set_title', 1);
		$jtci->title      = $this->params->get('jtci_title', JText::_('PLG_JTCI_MESSAGEBOX_TITLE'));
		$jtci->message    = $this->params->get('jtci_message', JText::_('PLG_JTCI_MESSAGEBOX_MESSAGE'));
		$jtci->closeTitle = $this->params->get('jtci_close_title', JText::_('PLG_JTCI_MESSAGEBOX_CLOSE_TITLE'));
		$jtci->legalURL   = $this->params->get('jtci_legal_url', '');
		$jtci->legalLabel = $this->params->get('jtci_legal_label', JText::_('PLG_JTCI_MESSAGEBOX_LEGAL_LABEL'));
		$jtci->legalTitle = $this->params->get('jtci_legal_title', JText::_('PLG_JTCI_MESSAGEBOX_LEGAL_TITLE'));

		if (isset($messageType[$jtci->theme][$this->params->get('jtci_message_type', 'dark')]))
		{
			$jtci->messageType = $messageType[$jtci->theme][$this->params->get('jtci_message_type', 'dark')];
		}
		else
		{
			$jtci->messageType = $this->params->get('jtci_message_type', 'dark');
		}

		if (!empty($jtci->legalURL))
		{
			$legalItemLanguage = $this->app->getMenu()->getItem($jtci->legalURL)->language;
			$activeLanguage    = JFactory::getLanguage()->getTag();

			if ($legalItemLanguage != $activeLanguage && $legalItemLanguage != '*')
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('k.id')
					->from('#__associations AS a')
					->where('a.id = ' . $db->q($jtci->legalURL));

				$query->select('k.key')
					->join('LEFT', '#__associations AS k ON k.id != ' . $db->q($jtci->legalURL) . ' AND a.key = k.key')
					->where('a.context="com_menus.item"');

				$query->select('m.id')
					->join('LEFT', '#__menu AS m ON m.id = a.id AND m.published=1 AND m.language = ' . $db->q($activeLanguage));

				$legalURL       = $db->setQuery($query)->loadResult();
				$jtci->legalURL = $legalURL;
			}
		}

		$jtci->activeLanguage = JFactory::getLanguage()->getTag();

		$this->jtci = $jtci;

		$strOutputHTML = $this->getTmpl($jtci->theme);

		$cookie = $this->app->input->cookie->get('jtci_accept', false, 'boolean');

		if ($cookie === false)
		{
			if (version_compare(JVERSION, '3', 'lt'))
			{
				$body = JResponse::getBody();
				$body = str_replace('</body>', $strOutputHTML . '</body>', $body);
				JResponse::setBody($body);
			}
			else
			{
				$body = $this->app->getBody();
				$body = str_replace('</body>', $strOutputHTML . '</body>', $body);
				$this->app->setBody($body);
			}
		}
	}

	/**
	 * getTmpl
	 *
	 * @param   string  $theme  Name of output templatefile without type
	 *
	 * @return   string  Templateoutput from selected framework
	 * @since    2.5
	 */
	protected function getTmpl($theme)
	{
		$path = $this->getTmplPath($theme);

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		include "$path";

		// Done with the requested template; get the buffer and
		// clear it.
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	/**
	 * getTmplPath
	 *
	 * @param   string  $filename  Name of output templatefile without type
	 * @param   string  $type      Type of templatefile
	 *
	 * @return   string  Path to output templatefile
	 * @since    2.5
	 */
	protected function getTmplPath($filename, $type = 'php')
	{
		$template = $this->app->getTemplate();

		// Build the template and base path for the layout
		$tAbsPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $this->_type . '_' . $this->_name . '/' . $filename . '.' . $type;
		$bAbsPath = JPATH_BASE . '/plugins/' . $this->_type . '/' . $this->_name . '/tmpl/' . $filename . '.' . $type;
		$dAbsPath = JPATH_BASE . '/plugins/' . $this->_type . '/' . $this->_name . '/tmpl/default.' . $type;

		// If the template has a layout override use it
		switch (true)
		{
			case (file_exists($tAbsPath)):
				return $tAbsPath;
				break;
			case (file_exists($bAbsPath)):
				return $bAbsPath;
				break;
			default:
				return $dAbsPath;
				break;
		}
	}
}
