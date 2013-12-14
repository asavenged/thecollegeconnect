<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace OcraCachedViewResolver\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

use OcraCachedViewResolver\Compiler\TemplateMapCompiler;

/**
 * Factory responsible of building a {@see \Zend\View\Resolver\TemplateMapResolver}
 * from cached template definitions
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CompiledMapResolverFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config            = $serviceLocator->get('Config');
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache             = $serviceLocator->get('OcraCachedViewResolver\\Cache\\ResolverCache');
        /* @var $originalResolver \Zend\View\Resolver\ResolverInterface */
        $originalResolver  = $serviceLocator->get('OcraCachedViewResolver\\Resolver\\OriginalResolver');
        $map               = $cache->getItem($config['ocra_cached_view_resolver']['cached_template_map_key'], $success);
        $aggregateResolver = new AggregateResolver();

        $aggregateResolver->attach($originalResolver, 50);

        if (! $success) {
            $compiler = new TemplateMapCompiler();
            $map      = $compiler->compileMap($originalResolver);

            $cache->setItem($config['ocra_cached_view_resolver']['cached_template_map_key'], $map);
        }

        $aggregateResolver->attach(new TemplateMapResolver($map), 100);

        return $aggregateResolver;
    }
}
