<?php
import('studs.actions.AbstractFormController');

/**
 * @package studs.actions
 */
class SimpleFormController extends AbstractFormController
{
	function &processFormSubmission(&$mapping, &$form, &$request, &$response) {
		return $this->onSubmit(&$mapping, &$form, &$request, &$response);
	}

	function &onSubmit(&$mapping, &$form, &$request, &$response) {
		$forward = $this->doSubmit($mapping, $form, $request);

		if ($forward != null) {
			return $forward;
		}
		else {
			return $mapping->findForward("successView");
		}
	}

	function &doSubmit(&$mapping, &$form, &$request) {
		$this->doSubmitAction($form, $request);
		return ref(null);
	}

	function &doSubmitAction(&$form, &$request) {
		;
	}
}
?>
