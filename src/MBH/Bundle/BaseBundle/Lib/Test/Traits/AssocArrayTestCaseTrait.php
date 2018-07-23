<?php


namespace MBH\Bundle\BaseBundle\Lib\Test\Traits;


trait AssocArrayTestCaseTrait
{
    /**
     * Asserts that two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $expected
     * @param array $actual
     */
    protected function assertArraySimilar(array $expected, array $actual): void
    {
        $this->assertCount(0, array_diff_key($actual, $expected));

        foreach ($expected as $key => $value) {
            if (\is_array($value)) {
                $this->assertArraySimilar($value, $actual[$key]);
            } else {
                $this->assertContains($value, $actual);
            }
        }
    }
}