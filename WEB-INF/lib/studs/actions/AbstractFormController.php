<?php
import('studs.action.Action');

/**
 * @package studs.actions
 */
class AbstractFormController extends Action
{
	function &execute(&$mapping, &$form, &$request, &$response)
	{
		if ($this->isFormSubmission($request)) {
			return $this->processFormSubmission(&$mapping, &$form, &$request, &$response);
		}
		else {
			return $this->showForm($mapping, $form, $request, $response);
		}
	}

	function isFormSubmission(&$request) {
		return "POST" == $request->getMethod();
	}

	function &processFormSubmission(&$mapping, &$form, &$request, &$response) {
	}

	function &prepareView(&$mapping, &$form, &$request, &$response) {
	}

	function &showForm(&$mapping, &$form, &$request, &$response) {
		$this->prepareView($mapping, $form, $request, $response);
		return $mapping->findForward("formView");
	}
}
?>
