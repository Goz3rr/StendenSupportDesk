<?php
	class TicketController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/tickets/create', 'TicketController::Create');
			$klein->respond('GET', '/tickets/view/[open|closed|new|newreplies:type]?/[i:id]?', 'TicketController::View');
		}

		public static function Create($request, $response, $service) {
			Auth::CheckLoggedIn();

			return View::Render('createticket');
		}

		public static function View($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($request->type == 'open') {
				return View::Render('opentickets');
			} elseif($request->type == 'closed') {
				return View::Render('closedtickets');
			} elseif($request->type == 'new') {
				return View::Render('newtickets');
			} elseif($request->type == 'newreplies') {
				return View::Render('newticketreplies');
			}
		}
	}