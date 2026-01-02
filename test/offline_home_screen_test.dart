import 'package:flutter_test/flutter_test.dart';
import 'package:flutter/material.dart';
import 'package:bold_beauty_lounge_beta/screens/home/offline_home_screen.dart';

void main() {
  testWidgets('OfflineHomeScreen builds without throwing', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: OfflineHomeScreen()));
    await tester.pump(const Duration(milliseconds: 200));
    expect(find.byType(ListView), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
