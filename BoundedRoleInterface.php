<?php
/**
 * This file is part of the Borobudur package.
 *
 * (c) 2017 Borobudur <http://borobudur.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Borobudur\Component\Dci;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface BoundedRoleInterface
{
    /**
     * Extract the data that bounded to role.
     *
     * @return object
     */
    public function extract(): object;
}
