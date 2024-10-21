<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Service;

use OCA\Talk\Chat\ChatManager;
use OCA\Talk\Manager as TalkManager;
use OCP\Security\RateLimiting\IRateLimitExceededException;
use OCA\Talk\Exceptions\ParticipantNotFoundException;
use OCA\Talk\Exceptions\RoomNotFoundException;
use Psr\Log\LoggerInterface;
use OCA\Talk\Model\Attendee;
use OCA\Talk\Service\ParticipantService;

class TalkService {
	/** @var TalkManager */
	protected $talkManager;
	/** @var ChatManager */
	protected $chatManager;
	/** @var ParticipantService */
	protected $participantService;
	/** @var LoggerInterface */
	private $logger;
	/** @var StoreService */
	private $storeService;

	public function __construct(
		LoggerInterface $logger,
		TalkManager $talkManager,
		ChatManager $chatManager,
		ParticipantService $participantService,
		StoreService $storeService
	) {
		$this->logger = $logger;
		$this->talkManager = $talkManager;
		$this->chatManager = $chatManager;
		$this->participantService = $participantService;
		$this->storeService = $storeService;
	}

	/**
	 * check if the talk integration is configured
	 * @return bool
	 */
	public function isConfigures() {
		$roomId = $this->storeService->getParameter('talkRoom');
		$userId = $this->storeService->getParameter('talkUser');

		if (!$roomId || !$userId) {
			$this->logger->error("Missing room parameter");
			return false;
		}

		try {
			$room = $this->talkManager->getRoomByToken($roomId);
			$participant = $this->participantService->getParticipant($room, $userId, false);
		} catch (RoomNotFoundException|ParticipantNotFoundException) {
			$this->logger->error("Talk user or room not found");
			return false;
		}
		return true;
	}

	/**
	 * Post the compare status to a talk chat room
	 * @param $message
	 * @return bool
	 * @throws IRateLimitExceededException
	 */
	public function postMessage($message) {
		$roomId = $this->storeService->getParameter('talkRoom');
		$userId = $this->storeService->getParameter('talkUser');
		$message = $this->cleanMessage($message);

		try {
			$room = $this->talkManager->getRoomByToken($roomId);
			$participant = $this->participantService->getParticipant($room, $userId, false);
		} catch (RoomNotFoundException|ParticipantNotFoundException) {
			$this->logger->error("Talk user or room not found or user not member of the room");
			return false;
		}

		$this->chatManager->sendMessage(
			$room,
			null,
			Attendee::ACTOR_USERS,
			$participant->getAttendee()->getActorId(),
			$message,
			new \DateTime()
		);
		return true;
	}

	/**
	 * Format the message
	 * @param $message
	 * @return string
	 */
	private function cleanMessage($message) {
		if ($message['counts']['- new transactions'] === 0) {
			return 'No new transactions';
		}

		$output = '';
		foreach ($message['counts'] as $key => $value) {
			$output .= "{$key}: {$value}\n";
		}

		$labels = [
			'new contacts' => 'new contacts',
			'new opportunities' => 'new opportunities',
			'updated opportunities' => 'updated opportunities'
		];

		foreach ($labels as $key => $label) {
			if (!empty($message[$key])) {
				$output .= $label . ': ' . implode(', ', $message[$key]) . '\n';
			}
		}
		return $output;
	}
}
