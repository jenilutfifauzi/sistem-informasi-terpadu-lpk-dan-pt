# UI Contract: CTK List Table

**Feature**: [spec.md](../spec.md) | **Plan**: [plan.md](../plan.md)  
**Contract Type**: User Interface  
**Component**: Filament Table (CTK List Page)  
**Date**: February 15, 2026

## Overview

This contract defines the presentation interface for the CTK list table after simplification. It specifies column structure, display formats, and user interactions.

## Table Columns Contract

### Column: NIK

- **Type**: Text
- **Label**: "NIK"
- **Data Source**: `ctk.nik`
- **Format**: Plain text
- **Features**: Searchable, Sortable, Copyable
- **Icon**: `heroicon-o-identification`
- **Required**: Yes

### Column: Nama Lengkap

- **Type**: Text
- **Label**: "Nama Lengkap"
- **Data Source**: `ctk.nama_lengkap`
- **Format**: Plain text with medium font weight
- **Features**: Searchable, Sortable
- **Required**: Yes

### Column: Status (MODIFIED)

- **Type**: Badge
- **Label**: "Status"
- **Data Source**: Computed from `ctk.completed_stages_count`
- **Display Logic**:
  ```
  IF completed_stages_count === 15:
      Text: "Lengkap"
      Color: success (green)
  ELSE:
      Text: "Belum Lengkap"
      Color: warning (orange/yellow)
  ```
- **Features**: Sortable
- **Sort Behavior**: Sorts by underlying `completed_stages_count` value
- **Required**: Yes
- **Possible Values**: 
  - `"Lengkap"` (exactly 15 stages complete)
  - `"Belum Lengkap"` (0-14 stages complete)

### Column: Entitas

- **Type**: Badge
- **Label**: "Entitas"
- **Data Source**: `ctk.current_entity`
- **Format**: Enum badge
- **Color Mapping**:
  - `LPK` → `info` (blue)
  - `PT` → `warning` (orange)
- **Features**: Sortable
- **Required**: Yes
- **Possible Values**: `"LPK"`, `"PT"`

### Column: Progress

- **Type**: Badge with description
- **Label**: "Progress"
- **Data Source**: `ctk.completion_progress` (accessor)
- **Format**: 
  - Main text: `"X/15"` (e.g., "8/15")
  - Description: `"Z%"` (e.g., "53%")
- **Color Logic**:
  ```
  IF completed_stages_count === 15: success (green)
  ELSE IF completed_stages_count >= 10: warning (orange)
  ELSE IF completed_stages_count >= 5: info (blue)
  ELSE: gray
  ```
- **Icon Logic**:
  ```
  IF completed_stages_count === 15: heroicon-o-check-circle
  ELSE: heroicon-o-clock
  ```
- **Features**: Sortable (by current_stage as proxy)
- **Required**: Yes

### Column: No. Telepon

- **Type**: Text
- **Label**: "No. Telepon"
- **Data Source**: `ctk.no_telepon`
- **Format**: Plain text
- **Icon**: `heroicon-o-phone`
- **Features**: Toggleable (can be hidden)
- **Required**: No (optional display)

### Column: Dibuat

- **Type**: DateTime
- **Label**: "Dibuat"
- **Data Source**: `ctk.created_at`
- **Format**: `d/m/Y H:i` (e.g., "15/02/2026 12:40")
- **Features**: Sortable, Toggleable
- **Required**: No (optional display)

## Removed Columns

### ~~Column: Tahap~~ (DELETED)

- **Status**: REMOVED
- **Rationale**: Redundant with Progress column
- **Migration Path**: Users should use Progress column for stage information

## Table Features

### Default Sort

- **Column**: `created_at`
- **Direction**: Descending (newest first)
- **Rationale**: Show most recent CTK entries first

### Filters

#### Entity Filter (unchanged)

- **Type**: SelectFilter
- **Label**: "Entitas"
- **Options**: LPK, PT
- **Behavior**: Filters `current_entity` field

#### Payment Status Filter (unchanged)

- **Type**: SelectFilter
- **Label**: "Status Pembayaran"
- **Options**: Belum Ada Pembayaran, Pembayaran Sebagian, Pembayaran Lunas
- **Behavior**: Filters based on payment relationship count

#### Date Range Filter (unchanged)

- **Type**: DateRangePicker
- **Label**: "Dibuat"
- **Fields**: "Dibuat Dari", "Dibuat Sampai"
- **Behavior**: Filters records by `created_at` range

#### ~~Stage Filter~~ (REMOVED)

- **Status**: DELETED
- **Rationale**: Removed along with Tahap column
- **Migration Path**: Users can filter by Progress visually or use Entity filter + manual scanning

### Row Actions

- **Click Behavior**: Navigate to view page (`CTKResource::getUrl('view', ['record' => $record])`)
- **Unchanged**: All existing row actions preserved

### Search

- **Searchable Fields**: NIK, Nama Lengkap
- **Behavior**: Global search across name and ID

## Responsive Behavior

### Desktop (>1024px)

- All columns visible by default
- Full table width with horizontal scroll if needed

### Tablet (768px - 1024px)

- Toggleable columns collapsed
- Core columns always visible: NIK, Nama Lengkap, Status, Progress

### Mobile (<768px)

- Card-based layout (Filament default)
- Priority fields: Nama Lengkap, Status, Progress

## Export Contract

### CSV/Excel Export

**Columns in Export**:
1. NIK
2. Nama Lengkap
3. Status (as "Lengkap" or "Belum Lengkap")
4. Entitas (as "LPK" or "PT")
5. Progress (as "X/15")
6. Percentage (as "Z%")
7. No. Telepon
8. Dibuat (formatted as ISO 8601 or configured format)

**Excluded from Export**:
- Tahap column (removed)

**Expected Behavior**: Export reflects current table view, including active filters

## Accessibility Requirements

### Color Independence

- Status must be readable without relying on color alone
- Text labels ("Lengkap" / "Belum Lengkap") provide semantic meaning
- Badge icons supplement color coding

### Screen Reader Support

- Column headers properly labeled
- Status badges announce text content
- Sort indicators accessible

### Keyboard Navigation

- Table rows focusable
- Sort headers keyboard-accessible
- Filters keyboard-accessible

## Error States

### Empty State

- **Condition**: No CTK records match current filters
- **Display**: "No records found" message
- **Actions**: Clear filters or create new CTK

### Loading State

- **Condition**: Fetching data
- **Display**: Skeleton loader for table rows
- **Duration**: < 500ms expected

### Error State

- **Condition**: Database query failure
- **Display**: Error notification banner
- **Recovery**: Reload page or contact admin

## Performance Contract

### Expected Performance

- **Initial Load**: < 500ms (for 100 records per page)
- **Sort Operation**: < 200ms
- **Filter Application**: < 300ms
- **Search**: < 400ms (with debounce)

### Pagination

- **Default**: 10 records per page
- **Options**: 10, 25, 50, 100
- **Behavior**: Standard Filament pagination

## Validation

### Pre-Conditions

1. User must have permission to view CTK list
2. User entity scoping applied (LPK sees stages 1-5, PT sees stages 6-15)

### Post-Conditions

1. Table displays 7 columns (not 8)
2. Status column shows "Lengkap" or "Belum Lengkap" only
3. Tahap column does not appear
4. Progress column continues to show "X/15 Z%" format
5. All existing filters work except removed Stage filter

## Breaking Changes

### For Users

- ❌ **Removed**: Tahap column no longer visible
- ❌ **Removed**: Stage filter no longer available
- ⚠️ **Changed**: Status column shows completion status instead of workflow stage name
- ✅ **Preserved**: Progress column provides stage detail

### For Developers

- ❌ **Removed**: `current_stage` column configuration from CTKSTable
- ⚠️ **Changed**: `current_status` column replaced with computed completion status
- ✅ **Preserved**: All model accessors unchanged
- ✅ **Preserved**: All database fields unchanged

## Test Cases

### User Acceptance Tests

1. **Complete CTK displays "Lengkap"**
   - Given: CTK with 15/15 stages complete
   - When: Viewing CTK list
   - Then: Status shows green "Lengkap" badge

2. **Incomplete CTK displays "Belum Lengkap"**
   - Given: CTK with < 15 stages complete
   - When: Viewing CTK list
   - Then: Status shows orange "Belum Lengkap" badge

3. **Tahap column not visible**
   - Given: Viewing CTK list
   - When: Examining table columns
   - Then: Tahap column does not appear

4. **Progress column shows detail**
   - Given: CTK with 8/15 stages complete
   - When: Viewing Progress column
   - Then: Shows "8/15" with "53%" description

5. **Status sortable**
   - Given: Multiple CTK records
   - When: Clicking Status column header
   - Then: Records sort by completion count (complete first or last depending on direction)

## Version

**Contract Version**: 1.0.0  
**Effective Date**: February 15, 2026  
**Supersedes**: Original CTK table contract (with Tahap column and current_status display)
