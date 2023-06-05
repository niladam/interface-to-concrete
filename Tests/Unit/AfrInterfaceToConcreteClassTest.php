<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use Autoframe\Components\Exception\AfrException;
use Autoframe\InterfaceToConcrete\Exception\AfrInterfaceToConcreteException;
use PHPUnit\Framework\TestCase;
use Autoframe\InterfaceToConcrete\AfrInterfaceToConcreteClass;


class AfrInterfaceToConcreteClassTest extends TestCase
{
    public static function getVendorPathProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $thirty_years = 3600 * 24 * 365 * 30;
        return [
            [[], 5, true, false],
            [['vendor'], $thirty_years, false, true],
            [[__DIR__], $thirty_years, false, true],
        ];
    }

    /**
     * @test
     * @dataProvider getVendorPathProvider
     */
    public function getVendorPathTest(array $aExtraPaths,
                                      int   $iAutoWireCacheExpireSeconds,
                                      bool  $bForceRegenerateAllButVendor,
                                      bool  $bGetSilenceErrors
    ): void
    {
        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);
        if (in_array(__DIR__, $aExtraPaths)) {
            AfrClassDependency::setSkipNamespaceInfo([''], true); //skip some global classes
        }

        $obj = null;
        try {
            $obj = new AfrInterfaceToConcreteClass(
                $aExtraPaths,
                $iAutoWireCacheExpireSeconds,
                $bForceRegenerateAllButVendor,
                $bGetSilenceErrors
            );

            $this->assertSame($obj->getForceRegenerateAllButVendor(), $bForceRegenerateAllButVendor, '!$bForceRegenerateAllButVendor');
            $this->assertSame($obj->getSilenceErrors(), $bGetSilenceErrors, '!$bGetSilenceErrors');
            $this->assertSame(true, strlen($obj->getHash()) > 5, '!getHash');
            $this->assertSame(true, count($obj->getPaths()) > 0, '!getPaths');
            $this->assertSame(true, $obj->getCacheExpire() > 0, '!getCacheExpire');
            $aMap = $obj->getClassInterfaceToConcrete();
            $this->assertSame(true, is_array($aMap), '!is_array($aMap)');
            $this->assertSame(true, count($aMap) > 10, '!count($aMap)');

            $i = 0;
            foreach ($aMap as $sFqcn => $aDeps) {
                if ($i > 2.4) {
                    break;
                }
                $this->assertSame(true, interface_exists($sFqcn) || class_exists($sFqcn), 'interface||class');
                $this->assertSame(true, is_array($aDeps) || is_bool($aDeps), '!is_array($aDeps)');
                if (!is_array($aDeps)) {
                    continue;
                }
                $i++;
                foreach ($aDeps as $sDfqcn => $bInstantiable) {
                    $this->assertSame(true, interface_exists($sDfqcn) || class_exists($sDfqcn), 'interface|2|class');
                    $this->assertSame(true, is_bool($bInstantiable), '!is_bool($bInstantiable)');
                    $i += 0.2;
                }
                break;
            }
        } catch (AfrException $e) {

        }
        $this->assertSame(true, $obj instanceof AfrInterfaceToConcreteClass, '!$obj instanceof AfrInterfaceToConcreteClass');
        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);
    }


}