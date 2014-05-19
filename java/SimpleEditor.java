

import javax.swing.*;
import java.awt.*;
import java.awt.event.*;



public class SimpleEditor {

	private static JFrame frame;
	private static JFileChooser fileChooser;



/**
 *
 */
public static void main(String[] args) {
	javax.swing.SwingUtilities.invokeLater(new Runnable() {
		public void run() {
			createAndShowGUI();
		}
	});
}


/**
 *
 */
private static void createAndShowGUI() {

	//Set the look and feel.
	try {
		UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
	}
	catch(Exception e) {
		System.err.println("Error setting look and feel.");
	}

	//Initialize global elements used later.
	fileChooser = new JFileChooser();

	//Make sure we have nice window decorations.
	JFrame.setDefaultLookAndFeelDecorated(true);

	//Create and set up the window.
	frame = new JFrame("Frame Title");
	frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
	frame.setLocationRelativeTo(null);
	//frame.setResizable(false);

	//Attach the menu bar.
	frame.setJMenuBar(createMenuBar());

	//Set up and attach the content pane.
	JPanel contentPane = new JPanel(new BorderLayout());
	contentPane.add(createComponents(), BorderLayout.CENTER);
	contentPane.setOpaque(true);
	frame.setContentPane(contentPane);

	//Display the window.
	frame.pack();
	frame.setVisible(true);
}


private static Component createComponents() {

}

private static JMenuBar createMenuBar() {

}















//End of SimpleEditor class.
}
