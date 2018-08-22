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

use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
abstract class AbstractContext
{
    /**
     * Execute context.
     *
     * @param array ...$args
     *
     * @return mixed
     */
    final public function execute(...$args)
    {
        $className = get_class($this);

        if (!method_exists($this, 'process')) {
            throw new RuntimeException(
                sprintf(
                    'Context "%s" should have process method',
                    $className
                )
            );
        }

        $process = new ReflectionMethod($className, 'process');

        if (!$process->isProtected()) {
            throw new RuntimeException('Method "process" should be protected');
        }

        return call_user_func_array([$this, 'process'], $args);
    }

    /**
     * Add a role to data.
     *
     * @param object       $data
     * @param AbstractRole $role
     *
     * @return BoundedRoleInterface|object
     */
    final protected function addRole(object $data, AbstractRole $role): BoundedRoleInterface
    {
        return new class ($role, $data) implements BoundedRoleInterface
        {
            use DelegatorTrait;

            public function __construct(AbstractRole $role, object $data)
            {
                if (false === $role->supports($data)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Role "%s" does not support for current data',
                            get_class($role)
                        )
                    );
                }

                $this->attach($this->invoke($role, 'attach', $data));
            }

            /**
             * Extract data from bounded roles.
             *
             * @return object
             */
            public function extract(): object
            {
                return $this->invoke($this->getInstance(), 'extract');
            }

            /**
             * Invoke closure method from role.
             *
             * @param AbstractRole $role
             * @param string       $method
             * @param array        ...$args
             *
             * @return mixed
             */
            private function invoke(AbstractRole $role, string $method, ...$args)
            {
                $method = new ReflectionMethod(get_class($role), $method);
                $method->setAccessible(true);

                return $method->invoke($role, ...$args);
            }
        };
    }

    /**
     * Add multiple roles to data.
     *
     * @param object $data
     * @param array  $roles
     *
     * @return BoundedRoleInterface|object
     */
    final protected function addRoles(object $data, array $roles): BoundedRoleInterface
    {
        foreach ($roles as $role) {
            $data = $this->addRole($data, $role);
        }

        return $data;
    }
}
