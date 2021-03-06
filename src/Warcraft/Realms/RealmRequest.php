<?php

/*
 * This file is part of the Battle.net API Client package.
 *
 * (c) Jonas Stendahl <jonas@stendahl.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pwnraid\Bnet\Warcraft\Realms;

use Pwnraid\Bnet\Core\AbstractRequest;
use Pwnraid\Bnet\Utils;

class RealmRequest extends AbstractRequest
{
    /**
     * @return BattlegroupEntity
     */
    public function battlegroups()
    {
        $data = $this->client->get('data/battlegroups/');

        return array_map(function ($battlegroup) {
            return new BattlegroupEntity($battlegroup);
        }, $data['battlegroups']);
    }

    /**
     * @param array|string $realms
     *
     * @return RealmEntity|array|null
     */
    public function find($realms)
    {
        $returnSingle = false;

        if (is_array($realms) === false) {
            $realms = [$realms];
            $returnSingle = true;
        }

        foreach ($realms as &$realm) {
            $realm = Utils::realmNameToSlug($realm);
        }

        $data = $this->client->get('realm/status', ['query' => ['realms' => implode(',', $realms)]]);
        $realmCount = count($data['realms']);

        if ($returnSingle === true && $realmCount !== 1) {
            return;
        }

        if ($realmCount !== count($realms)) {
            throw new \RuntimeException('Unable to fetch all requested realms');
        }

        $realms = $this->createRealmEntities($data['realms']);

        return ($returnSingle === true) ? $realms[0] : $realms;
    }

    /**
     * @return array
     */
    public function all()
    {
        $data = $this->client->get('realm/status');

        return $this->createRealmEntities($data['realms']);
    }

    /**
     * @param array $realmsList
     *
     * @return array
     */
    protected function createRealmEntities(array $realmsList)
    {
        return array_map(function ($realm) {
            return new RealmEntity($realm);
        }, $realmsList);
    }
}
