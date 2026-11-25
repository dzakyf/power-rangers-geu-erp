<?php

namespace Tests\Unit\CNS;

use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Tests\TestCase;

class DhtmlxGridPaginationTest extends TestCase
{
    public function test_get_page_size_defaults_and_validation()
    {
        $reqEmpty = new Request();
        $this->assertSame(25, DhtmlxGridPagination::getPageSize($reqEmpty));

        $req10 = new Request(['count' => 10]);
        $this->assertSame(10, DhtmlxGridPagination::getPageSize($req10));

        $req50 = new Request(['count' => 50]);
        $this->assertSame(50, DhtmlxGridPagination::getPageSize($req50));

        $req100 = new Request(['count' => 100]);
        $this->assertSame(100, DhtmlxGridPagination::getPageSize($req100));

        // Invalid or fallback values
        $reqInvalid = new Request(['count' => 999]);
        $this->assertSame(500, DhtmlxGridPagination::getPageSize($reqInvalid));

        $reqNegative = new Request(['count' => -5]);
        $this->assertSame(25, DhtmlxGridPagination::getPageSize($reqNegative));
    }

    public function test_get_pos_start_from_pos_start_or_page()
    {
        $reqDefault = new Request();
        $this->assertSame(0, DhtmlxGridPagination::getPosStart($reqDefault));

        $reqPos = new Request(['posStart' => 50]);
        $this->assertSame(50, DhtmlxGridPagination::getPosStart($reqPos));

        $reqPage = new Request(['page' => 3]);
        $this->assertSame(50, DhtmlxGridPagination::getPosStart($reqPage, 25));

        $reqPageSize10 = new Request(['page' => 2]);
        $this->assertSame(10, DhtmlxGridPagination::getPosStart($reqPageSize10, 10));
    }

    public function test_paginate_collection()
    {
        $items = collect(range(1, 75));
        $req = new Request(['posStart' => 25, 'count' => 25]);

        $res = DhtmlxGridPagination::paginateCollection($items, $req);

        $this->assertSame(75, $res['total']);
        $this->assertSame(25, $res['posStart']);
        $this->assertSame(25, $res['count']);
        $this->assertCount(25, $res['rows']);
        $this->assertSame(26, $res['rows'][0]);
        $this->assertSame(50, $res['rows'][24]);
    }

    public function test_render_xml_response_and_userdata()
    {
        $body = "  <row id=\"1\"><cell>1</cell></row>\n";
        $response = DhtmlxGridPagination::response(100, 0, $body, ['total_qty' => '1,250.00']);

        $content = $response->getContent();
        $this->assertStringContainsString('<rows total_count="100" pos="0">', $content);
        $this->assertStringContainsString('<userdata name="total_qty">1,250.00</userdata>', $content);
        $this->assertStringContainsString('<row id="1"><cell>1</cell></row>', $content);
        $this->assertStringContainsString('</rows>', $content);
        $this->assertSame('text/xml', $response->headers->get('Content-Type'));
    }

    public function test_paginate_query_applies_filter_map_and_col_map()
    {
        $fakeQuery = new class {
            public $wheres = [];
            public $orders = [];
            public $offsetVal;
            public $limitVal;

            public function where($col, $op = null, $val = null)
            {
                $this->wheres[] = [$col, $op, $val];
                return $this;
            }

            public function orderBy($col, $dir = 'asc')
            {
                $this->orders[] = [$col, $dir];
                return $this;
            }

            public function count()
            {
                return 42;
            }

            public function offset($val)
            {
                $this->offsetVal = $val;
                return $this;
            }

            public function limit($val)
            {
                $this->limitVal = $val;
                return $this;
            }

            public function get()
            {
                return collect(['row1']);
            }
        };

        $req = Request::create('/', 'GET', [
            'posStart' => 25,
            'count' => 25,
            'sort_col' => 2,
            'sort_dir' => 'desc',
            'filter' => [
                1 => 'abc',
                2 => 'yes',
                99 => 'ignored',
                3 => '   ',
            ],
        ]);

        $res = DhtmlxGridPagination::paginateQuery($fakeQuery, $req, [
            'col_map' => [2 => 'Company_Name'],
            'filter_map' => [
                1 => 'Supplier_ID',
                2 => function ($q, $term) {
                    $q->where('Is_Consignee', '=', $term === 'yes' ? 1 : 0);
                },
                3 => 'City',
            ],
        ]);

        $this->assertSame([['Supplier_ID', 'like', '%abc%'], ['Is_Consignee', '=', 1]], $fakeQuery->wheres);
        $this->assertSame([['Company_Name', 'desc']], $fakeQuery->orders);
        $this->assertSame(25, $fakeQuery->offsetVal);
        $this->assertSame(25, $fakeQuery->limitVal);
        $this->assertSame(42, $res['total']);
    }
}
